import paho.mqtt.client as mqtt
import time
import json

from config.db_python.db_config import DBConfig

from services.client.donnee_capteur_service.donnee_capteur_service import DonneeCapteurService
from services.client.led_service.led_service import LedService
from services.client.telegram_service.telegram_service import TelegramService
from services.client.seuillage.seuillage_service import SeuillageService

# Grâce au fonction "join_room" que nous avons importé ci-dessous. Cela va permettre au serveur flask de gérer
# de manière plus intelligent la publication des infos/données capteurs sur le canal de communication websocket.
#
# Cela dit en fonction de l'id_parc que l'utilisateur a en sa possession et ainsi qu'à son choix_parcelle sur l'interface streamlit
# que les données capteurs qu'il va recevoir s'adapte. Pour l'affichage en temps réelle de la courbe du diagramme pour un id_parc spécifique choisi par l'utilisateur
#
# Les données capteurs seront envoyées dans un room spécifique à travers le canal de communication websocket
from flask_socketio import SocketIO, emit, join_room

class RecuperateDataSensorMQTT():

    def __init__(self):
        self.db_config = DBConfig()
        self.donneeCapteurService = DonneeCapteurService()
        self.ledService = LedService()
        self.telegramService = TelegramService()
        self.seuillageService = SeuillageService(self.db_config)
        
        self.ip_arduino = "192.168.100.125" #"10.47.121.125" #"11.0.0.125" #"192.168.100.125"

        self.delai_conf = 10
        
        self.delais_seconde = 45
        self.dernier_enregistrement = 0
        self.temps_actuelle = None
        self.alerte_to_save = ""
        self.msg_to_save = ""
        self.derniere_insertion_time = 0

        # On a mis la valeur du status_envoie_notif sur 1 (vrai) initialement pour éviter tout éventuel erreur 
        # de déclenchement de l'envoi de notification
        self.status_envoie_notif = 1 # valeur booléen 1 -> vrai et 0 -> faux
        self.duree_arrosage_auto = 10
        self.topic_etat_pompe = "status/pompe"

        # Dictionnaire pour stocker la durée d'arrosage souhaitée pour chaque parcelle
        self.duree_arrosage_pompe = {
            "OP_001": self.duree_arrosage_auto,
            "OP_002": self.duree_arrosage_auto,
            "OP_003": self.duree_arrosage_auto,
            "OP_004": self.duree_arrosage_auto
        }

        self.derniere_donnees_capteurs = {
            "OP_001": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0},
            "OP_002": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0},
            "OP_003": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0},
            "OP_004": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0}
        }

        self.derniere_donnees_capteurs_conf = {
            "OP_001": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0},
            "OP_002": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0},
            "OP_003": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0},
            "OP_004": {"temperature": 0.0, "luminosite": 0.0, "humidite": 0}
        }

        self.etat_pompe_dict = {
            "OP_001": "Desactive",
            "OP_002": "Desactive",
            "OP_003": "Desactive",
            "OP_004": "Desactive"
        }
        
        # La valeur dans ce dictionnaire sert à capturer l'état pour tentative d'activation de la pompe 
        # lorsque l'utilisateur se trouve dans une mauvaise condition d'arrosage (ex: TERRE SECHE, TERRE TROP HUMIDE)
        # Cela servira de réaliser le scénario suivant : 
        # L'utilisateur se trouve dans une mauvaise condition (ex : humidite > 60 (terre trop humide) | temperature < 30 | luminosite < 10000) mais
        # il tente quand même de réaliser une activation de la pompe. En 1er temps : une notification lui sera envoyé qu'une seule fois (pas plus tant qu'il est dans la même condition)
        # pour servir d'info. Dans cette notification le système lui demande de refaire un second tentative pour confirmer son choix.
        self.tentatives_arrosage = {
            "OP_001": False,
            "OP_002": False,
            "OP_003": False,
            "OP_004": False
        }

        self.horaire_arrosage = {
            "OP_001": None,
            "OP_002": None,
            "OP_003": None,
            "OP_004": None
        }

        self.dernier_seuil_enregistre = {
            "OP_001": [None, None], # valeur à l'indice 0 -> seuil humidite | valeur à l'indice 1 -> seuil température
            "OP_002": [None, None],
            "OP_003": [None, None],
            "OP_004": [None, None]
        }

        self.mode_systeme_cache = {}
        
        # Initialisation du client MQTT pour pouvoir l'utiliser dans les threads
        self.mqtt_client = mqtt.Client()
        self.mqtt_client.on_connect = self.on_connect
        self.mqtt_client.on_message = self.on_message
        self.mqtt_client.connect("11.0.0.22", 1883) #("11.0.0.116", 1883) #("192.168.100.117", 1883)
        self.mqtt_client.loop_start()

    # Fonction appelée lorsque le client MQTT se connecte au broker MQTT
    def on_connect(self, client, userdata, flags, rc):
        print("Connecté au broker MQTT avec le code : " + str(rc))
        client.subscribe("data/temperature/#")
        client.subscribe("data/luminosite/#")
        client.subscribe("data/humidite/#")
        client.subscribe("action/led/status")

    # --------------- Fonction pour la récupération du mode du système stocké en base de donnée ------------------------
    def select_mode_systeme(self, id_parc):
        conn, cursor = self.db_config.connect()

        if conn and cursor:
            try:
                sql_select = "SELECT Operation_mode FROM mode_systeme WHERE Id_parc = %s"
                value = [id_parc]

                cursor.execute(sql_select, value)
                mode_systeme_actuel = cursor.fetchone()
                
                # fetchone() retourne un tuple, on récupère le premier élément
                if mode_systeme_actuel:
                    return mode_systeme_actuel[0], 200 # Retourne "Auto" ou "Manuel"

                return "Manuel", 400 # Valeur par défaut

            except Exception as e:
                print(f"Erreur lors de la récupération du mode : {e}")
                return "Manuel", 400

            finally:
                self.db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return "Manuel", 400
    # --------------- Fonction pour la récupération du mode du système stocké en base de donnée ------------------------

    # --------------- Fonction de confirmation/validation donnée capteur avant d'effectuer une action ------------------
    def verifier_delai_confirmation(self, id_parc):
        if self.horaire_arrosage[id_parc] is None:
            # On met à jour la valeur de la variable globale "self.horaire_arrosage[id_parc]"
            self.horaire_arrosage[id_parc] = time.time()
            return False
        else:
            # On calcule le temps écoulé depuis le début du chronomètre
            temps_ecoule = time.time() - self.horaire_arrosage[id_parc]

            if temps_ecoule > self.delai_conf:
                # Délai dépassé, on réinitialise pour la prochaine fois et on valide
                self.horaire_arrosage[id_parc] = None
                return True # True -> pour confirmer la validation du donnée reçu du capteur
            return False

    # ----------------------------- Fonction pour activer l'arrosage en mode AUTO --------------------------------------
    def execution_arrosage_auto(self, id_parc, duree_arrosage):
        status_code = self.ledService.led_on_thread(id_parc, self.mqtt_client, duree_arrosage)

        if status_code == 200:
            print(f"[SYSTEM AUTO] Arrosage démarré pour {id_parc}")
        else:
            print(f"[ERREUR AUTO] L'allumage pour {id_parc} a échoué (Code {status_code}). L'extinction n'est pas programmée.")

    # -------------------------- Fonction appelée à chaque message reçu du broker MQTT ---------------------------------
    def on_message(self, client, userdata, msg):
        topic = msg.topic
        
        # ----------------- Pour capturer le status_code envoyé par l'arduino après avoir effectué une action (activer ou désactiver la led)
        if topic == "action/led/status":
            try:
                msg_json_status_action = json.loads(msg.payload.decode("utf-8"))
                status_code = msg_json_status_action.get("Status_code", 400)
                id_parc = msg_json_status_action.get("Id_parc", "")
                action_arduino = msg_json_status_action.get("Action", "")

                if action_arduino is None:
                    print("Oups ! L'Arduino a oublié de dire quelle action il a faite.")
                    return # On s'arrête sans faire crasher tout le script

                if int(status_code) == 200:
                    print("L'action a réussi sur l'Arduino !\n")
                    print(f"Document Json reçu de l'ESP32 : \"Status_code\": {status_code}, \"Id_parc\": {id_parc}, \"action_arduino\": {action_arduino}\n")

                    if action_arduino == "ON":
                        # Mise à jour de l'état de la pompe a stocker dans le dictionnaire
                        self.etat_pompe_dict[id_parc] = "Active"
                        self.mqtt_client.publish (f"{self.topic_etat_pompe}/{id_parc}", f"{self.etat_pompe_dict[id_parc]}")
                    
                    elif action_arduino == "OFF":
                        self.etat_pompe_dict[id_parc] = "Desactive"
                        self.mqtt_client.publish (f"{self.topic_etat_pompe}/{id_parc}", f"{self.etat_pompe_dict[id_parc]}")

                else:
                    print("L'action a échoué sur l'Arduino !\n")
                    print(f"Erreur Arduino ({status_code}) sur {id_parc}\n")

            except Exception as e:
                print(f"L'Arduino a oublié de retourner sa réponse sur l'action qu'il a prise !")
                return 
        # ----------------- Pour capturer le status_code envoyé par l'arduino après avoir effectué une action (activer ou désactiver la led)
        
        # ----------------- Pour capturer donnée capteur -----------------
        if topic.startswith("data/"):
            # On va découper la chaîne de caractère (topic) à chaque '/'
            elements = topic.split('/')

            if len(elements) >= 3:
                type_capteur = elements[1] # Pour récupérer le deuxième élément (index 1). Ex : data/temperature/OP_001 -> "temperature"
                id_parc = elements[2] # Pour récupérer le troisième élément (index 2). Ex : data/temperature/OP_001 -> "OP_001"

                msg_json_donnee_capteur = json.loads(msg.payload.decode("utf-8"))

                if type_capteur in msg_json_donnee_capteur:
                    self.derniere_donnees_capteurs[id_parc][type_capteur] = msg_json_donnee_capteur[type_capteur] # Pour récupérer la valeur de clé du document json renvoyer par l'Arduino UNO
                    # via la communication MQTT. Ex: {"temperature": 25.15} -> on récupère "25.15"
            
            temp = self.derniere_donnees_capteurs[id_parc]["temperature"]
            luminosite = self.derniere_donnees_capteurs[id_parc]["luminosite"]
            humidite = self.derniere_donnees_capteurs[id_parc]["humidite"]

            # ---------------------------------- MODE AUTO --------------------------------
            # On ne consulte la base de donnée que si la parcelle n'est pas en cache
            if id_parc not in self.mode_systeme_cache:
                mode_systeme_actuel, status_code = self.select_mode_systeme(id_parc)

                if status_code == 200:
                    self.mode_systeme_cache[id_parc] = mode_systeme_actuel
                
                else:
                    self.mode_systeme_cache[id_parc] = "Auto" # Valeur par défaut en cas d'erreur
                
            mode_systeme_actuel = self.mode_systeme_cache[id_parc]


            if mode_systeme_actuel == "Auto":
                #list_id_parc = [f"{id_parc}"]

                # Cette condition est vrai à l'état de départ du programme et au moment où l'utilisateur modifie la valeur des seuils
                if self.dernier_seuil_enregistre[id_parc][0] is None and self.dernier_seuil_enregistre[id_parc][1] is None:
                    liste_seuil_data, status_code = self.seuillageService.selectSeuilData(id_parc)
                
                    if status_code == 200 and liste_seuil_data:
                        for seuil_data in liste_seuil_data:
                            Id_seuil, Id_parc, Temp_seuil, Humidite_seuil = seuil_data

                            # Stockage des nouvelles valeurs de seuil dans le cache (dans le dictionnaire)
                            self.dernier_seuil_enregistre[id_parc][0] = Humidite_seuil
                            self.dernier_seuil_enregistre[id_parc][1] = Temp_seuil
                    
                    else:
                        print("[Erreur] Problème de récupération de donnée de seuillage stocké en base de donnée !")
                        self.status_envoie_notif = 0

                        if self.status_envoie_notif == 0:
                            type_alerte = "ERREUR"
                            
                            msg_final_send = (
                                f"<b>{type_alerte}</b>\n\n"
                                f"<b>{id_parc}</b> -> Echec de l'activation de l'arrosage auto !!!\n\n"
                                f"<b>Problème de récupération de donnée de seuillage stocké en base de donnée</b>"
                            )

                            self.telegramService.envoyer_notification_telegram(id_parc, self.db_config, msg_final_send)
                            self.status_envoie_notif = 1

                # Utilise les valeurs du cache (sans requête DB)
                Humidite_seuil = self.dernier_seuil_enregistre[id_parc][0]
                Temp_seuil = self.dernier_seuil_enregistre[id_parc][1]

                if temp > Temp_seuil and humidite < Humidite_seuil:
                    self.status_envoie_notif = 0

                    if self.verifier_delai_confirmation(id_parc):
                        if self.status_envoie_notif == 0:
                            type_alerte = "Avertissement"
                            msg_str = f"<b>{id_parc} -> LIMITE SEUIL DEPASSE :</b> <br><br><ul><li><i>{temp} > Temp_seuil</i></li> & <li><i>{humidite} > Humidite_seuil</i></li></ul>"

                            msg_final_send = (
                                f"<b>{type_alerte}</b><br><br>"
                                f"<i>{msg_str}</i><br><br>"
                                f"<i>L'arrosage va s'activer dans <b><{self.duree_arrosage_pompe[id_parc]}</b>sec....</i>"
                            )

                            self.telegramService.envoyer_notification_telegram(id_parc, self.db_config, msg_final_send)
                            self.status_envoie_notif = 1
                        
                        # Une fois que la notification envoyé nous allons procédé à l'activation planifié de la pompe pour l'arrosage
                        self.execution_arrosage_auto(id_parc, self.duree_arrosage_pompe[id_parc])

                #if temp > 30 and luminosite > 10000:
                #    if humidite < 15:

                #        self.status_envoie_notif = 0

                #        if self.verifier_delai_confirmation(id_parc):
                #            if self.status_envoie_notif == 0:
                #                type_alerte = "Urgence"
                #                msg_str = f"<b>{id_parc} -> FORTE CHALEUR :</b> Le soleil est trop fort. L'arrosage automatique est suspendu pour éviter que l'eau ne s'évapore. Pensez à mettre vos plantes à l'ombre."

                #                msg_final_send = (
                #                    f"<b>Type_alerte:</b> {type_alerte}\n\n"
                #                    f"{msg_str}\n\n"
                #                    f"<i>Recommandation:</i> Penser à planifier un arrosage dans la soirée."
                #                )

                #                self.telegramService.envoyer_notification_telegram(msg_final_send)
                #                self.status_envoie_notif = 1

                        #else:
                        #    if self.status_envoie_notif == 0:
                        #        print(f"Alerte annulée pour {id_parc}")
                        #        self.horaire_arrosage[id_parc] = None
                        #        self.status_envoie_notif = 1
                #    else:

                #        self.status_envoie_notif = 0

                #        if self.verifier_delai_confirmation(id_parc):                            
                #            if self.status_envoie_notif == 0:
                #                type_alerte = "Avertissement"
                #                msg_str = f"<b>{id_parc} -> CANICULE :</b> L'arrosage automatique est suspendu car il fait trop chaud. L'humidité de votre terre est suffisante pour l'instant."

                #                msg_final_send = (
                #                    f"<b>Type_alerte:</b> {type_alerte}\n\n"
                #                    f"{msg_str}\n\n"
                #                    f"<i>Recommandation:</i> Abriter les plantes sous une bâche ou support quelconque."
                #                )

                #                self.telegramService.envoyer_notification_telegram(msg_final_send)
                #                self.status_envoie_notif = 1

                        #else:
                        #    if self.status_envoie_notif == 0:
                        #        print(f"Alerte annulée pour {id_parc}")
                        #        self.horaire_arrosage[id_parc] = None
                        #        self.status_envoie_notif = 1

                #if humidite < 15 and 15 < temp < 28:

                #    self.status_envoie_notif = 0

                #    if self.verifier_delai_confirmation(id_parc):
                #        if self.status_envoie_notif == 0:
                #            type_alerte = "Info"
                #            msg_str = f"<b>{id_parc} -> TERRE SECHE :</b> L'arrosage automatique démarre dans <b>{self.sec_activation_pompe} secondes</b>. Veuillez vous éloigner de la parcelle."

                #            msg_final_send = (
                #                f"<b>Type_alerte:</b> {type_alerte}\n\n"
                #                f"{msg_str}\n\n"
                #            )

                #            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            
                            # Remplacement du Timer par une logique de démarrage à retardement via calcul de temps
                            # On réutilise verifier_delai_confirmation mais pour déclencher l'activation
                            # NOTE: Ici l'utilisateur veut aussi retarder l'activation. 
                            # Pour rester simple, on va garder le Timer uniquement pour le RETARD d'activation si nécessaire,
                            # OU alors on utilise une autre variable de "debut_attente_activation".
                            # Mais le user a dit "remplacer TOUT l'utilisation de threading.Timer".
                            
                            # On va utiliser une logique similaire pour le délai d'activation
                #            self.execution_arrosage_auto(id_parc, self.duree_arrosage_pompe[id_parc])
                #            self.status_envoie_notif = 1

                    #else:
                    #    if self.status_envoie_notif == 0:
                    #        print(f"Alerte annulée pour {id_parc}")
                    #        self.horaire_arrosage[id_parc] = None
                    #        self.status_envoie_notif = 1

                #if humidite > 60 and temp > 22:

                #    self.status_envoie_notif = 0

                #    if self.verifier_delai_confirmation(id_parc):
                #        if self.status_envoie_notif == 0:
                #            type_alerte = "Avertissement"
                #            msg_str = f"<b>{id_parc} -> TERRE TROP MOUILLEE :</b> L'arrosage automatique est suspendu. La terre est saturée d'eau. Trop d'humidité risquerait de faire pourrir vos plantes."

                #            msg_final_send = (
                #                f"<b>Type_alerte:</b> {type_alerte}\n\n"
                #                f"{msg_str}\n\n"
                #                f"<i>Recommandation:</i> Eviter d'activer la pompe pour ne pas tuer votre plantation."
                #            )

                #            self.telegramService.envoyer_notification_telegram(msg_final_send)
                #            self.status_envoie_notif = 1

                    #else:
                    #    if self.status_envoie_notif == 0:
                    #        print(f"Alerte annulée pour {id_parc}")
                    #        self.horaire_arrosage[id_parc] = None
                    #        self.status_envoie_notif = 1

                #if 20 <= humidite <= 50 and 18 < temp < 26 and luminosite > 5000:

                #    self.status_envoie_notif = 0

                #    if self.verifier_delai_confirmation(id_parc):
                #        if self.status_envoie_notif == 0:
                #            type_alerte = "Succès"
                #            msg_str = f"<b>{id_parc} -> TOUT VA BIEN :</b> L'humidité et la température sont parfaites. Vos plantes poussent dans des conditions idéales."

                #            msg_final_send = (
                #                f"<b>Type_alerte:</b> {type_alerte}\n\n"
                #                f"{msg_str}\n\n"
                #            )

                #            self.telegramService.envoyer_notification_telegram(msg_final_send)
                #            self.status_envoie_notif = 1

                    #else:
                    #    if self.status_envoie_notif == 0:
                    #        print(f"Alerte annulée pour {id_parc}")
                    #        self.horaire_arrosage[id_parc] = None
                    #        self.status_envoie_notif = 1

                #if luminosite < 100:

                #    self.status_envoie_notif = 0

                #    if self.verifier_delai_confirmation(id_parc):
                #        if self.status_envoie_notif == 0:
                #            type_alerte = "Info"
                #            msg_str = f"<b>{id_parc} -> REPOS NOCTURNE :</b> Le système surveille votre parcelle en attendant le réveil de vos plantes. Bonne nuit !"

                #            msg_final_send = (
                #                f"<b>Type_alerte:</b> {type_alerte}\n\n"
                #                f"{msg_str}\n\n"
                #            )

                #            self.telegramService.envoyer_notification_telegram(msg_final_send)
                #            self.status_envoie_notif = 1

                    #else:
                    #    if self.status_envoie_notif == 0:
                    #        print(f"Alerte annulée pour {id_parc}")
                    #        self.horaire_arrosage[id_parc] = None
                    #        self.status_envoie_notif = 1

            # ---------------------- DELAIS D'ATTENTE AVANT L'INSERTION DE NOUVEAU DONNEE CAPTEUR DANS LA BASE DE DONNEE --------------------------
        else:
            self.alerte_to_save = "N/A"
            self.msg_to_save = "Donne capteur non intercepter !"

        # ----------------- Pour capturer donnée capteur -----------------
        self.temps_actuelle = time.time()
        if (self.temps_actuelle - self.derniere_insertion_time) >= self.delais_seconde:
            try:
                status_code = self.donneeCapteurService.create_donnee_capteur(id_parc, humidite, temp, luminosite, self.db_config)

                if status_code == 200:
                    print(f"[BDD] Temps écoulé. Donnée {humidite}% - {temp}°C - {luminosite}lx enregistrée pour id_parc={id_parc}.")

                    self.derniere_insertion_time = self.temps_actuelle
                
                else:
                    print(f"[BDD] Erreur survenu lors de la tentative d'insertion de donnée capteur dans la base de donnée pour id_parc={id_parc} !")

            except Exception as e:
                print(f"[ERREUR BDD] : {e}")
        #else:
        #    print(f"[INFO] Insertion ignorée (attente de {self.delais_seconde}s en cours...)")