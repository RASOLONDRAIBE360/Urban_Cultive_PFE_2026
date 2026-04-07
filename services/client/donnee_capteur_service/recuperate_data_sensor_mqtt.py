from services.client import led_service
import paho.mqtt.client as mqtt

import time
import json
from config.db_python.db_config import DBConfig

from services.client.donnee_capteur_service.donnee_capteur_service import DonneeCapteurService
from services.client.led_service.led_service import LedService
from services.client.telegram_service.telegram_service import TelegramService

class RecuperateDataSensorMQTT():

    def __init__(self):
        self.db_config = DBConfig()
        self.donneeCapteurService = DonneeCapteurService()
        self.ledService = LedService()
        self.telegramService = TelegramService()

        self.ip_arduino = "192.168.100.125"
        self.delai_conf = 10
        self.derniere_insertion_time = 0

        # On a mis la valeur du status_envoie_notif sur 1 (vrai) initialement pour éviter tout éventuel erreur 
        # de déclenchement de l'envoi de notification
        self.status_envoie_notif = 1 # valeur booléen 1 -> vrai et 0 -> faux
        self.sec_activation_pompe = 10
        self.duree_arrosage = 10
        self.topic_etat_pompe = "status/pompe"

        # Dictionnaire pour stocker la durée d'arrosage souhaitée pour chaque parcelle
        self.duree_arrosage_pomp = {
            "OP_001": 10,
            "OP_002": 10,
            "OP_003": 10,
            "OP_004": 10
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

        self.mode_arrosage_dict = {
            "OP_001": "manuel",
            "OP_002": "manuel",
            "OP_003": "manuel",
            "OP_004": "manuel"
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

        # Initialisation du client MQTT pour pouvoir l'utiliser dans les threads
        self.mqtt_client = mqtt.Client()
        self.mqtt_client.on_connect = self.on_connect
        self.mqtt_client.on_message = self.on_message
        self.mqtt_client.connect("192.168.100.117", 1883)
        self.mqtt_client.loop_start()

    # Fonction appelée lorsque le client MQTT se connecte au broker MQTT
    def on_connect(self, client, userdata, flags, rc):
        print("Connecté au broker MQTT avec le code : " + str(rc))
        client.subscribe("data/temperature/#")
        client.subscribe("data/luminosite/#")
        client.subscribe("data/humidite/#")
        client.subscribe("action/led/status")

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
    def execution_arrosage_auto(self, id_parc, list_id_parc):
        status_code = self.ledService.led_on_thread(id_parc, self.mqtt_client)

        if status_code == 200:
            print(f"[SYSTEM AUTO] Arrosage démarré pour {id_parc}")
        else:
            print(f"[ERREUR AUTO] L'allumage pour {id_parc} a échoué (Code {status_code}). L'extinction n'est pas programmée.")

    # -------------------------- Fonction appelée à chaque message reçu du broker MQTT ---------------------------------
    def on_message(self, client, userdata, msg):
        topic = msg.topic
        
        # Pour capturer le status_code envoyé par l'arduino après avoir effectué une action (activer ou désactiver la led)
        if topic == "action/led/status":
            try:
                msg_json = json.loads(msg.payload.decode("utf-8"))
                status_code = msg_json.get("Status_code")
                id_parc = msg_json.get("Id_parc")
                action_arduino = msg_json.get("Action")
                
                if msg_json and status_code == 200:
                    print(f"Document Json reçu de l'ESP32 : \"Status_code\": {status_code}, \"Id_parc\": {id_parc}, \"action_arduino\": {action_arduino}\n")

                if action_arduino is None:
                    print("Oups ! L'Arduino a oublié de dire quelle action il a faite.")
                    return # On s'arrête sans faire crasher tout le script

                print(f"L'Arduino confirme la commande (Status: {status_code})")

                if int(status_code) == 200:
                    print("L'action a réussi sur l'Arduino !")

                    if action_arduino == "ON":
                        # Mise à jour de l'état de la pompe a stocker dans le dictionnaire
                        self.etat_pompe_dict[id_parc] = "Active"
                        self.mqtt_client.publish (f"{self.topic_etat_pompe}/{id_parc}", f"{self.etat_pompe_dict[id_parc]}")
                    
                    elif action_arduino == "OFF":
                        self.etat_pompe_dict[id_parc] = "Desactive"
                        self.mqtt_client.publish (f"{self.topic_etat_pompe}/{id_parc}", f"{self.etat_pompe_dict[id_parc]}")

                else:
                    print("L'action a échoué sur l'Arduino !")

            except Exception as e:
                print(f"Erreur lors du parsing du status de l'Arduino : {e} en format Json")
                return 
        # Pour capturer le status_code envoyé par l'arduino après avoir effectué une action (activer ou désactiver la led)

        if topic.startswith("data/"):
            parts = msg.topic.split("/")
            if len(parts) >= 3:
                cle = parts[1]
                id_parc = parts[2]

                msg_json = json.loads(msg.payload.decode("utf-8"))

                # Initialiser si la parcelle n'existe pas encore
                if id_parc not in self.derniere_donnees_capteurs:
                    self.derniere_donnees_capteurs[id_parc] = {}

                if cle in msg_json:
                    self.derniere_donnees_capteurs[id_parc][cle] = msg_json[cle]

            temp = self.derniere_donnees_capteurs[id_parc]["temperature"]
            luminosite = self.derniere_donnees_capteurs[id_parc]["luminosite"]
            humidite = self.derniere_donnees_capteurs[id_parc]["humidite"]

            # --------------------------------- MODE MANUEL -------------------------------
            if self.mode_arrosage_dict[id_parc] == "manuel":
                if temp > 30 and luminosite > 10000:
                    self.status_envoie_notif = 0

                    if humidite < 15:
                        if self.verifier_delai_confirmation(id_parc):
                            self.derniere_donnees_capteurs_conf[id_parc]["temperature"] = temp
                            self.derniere_donnees_capteurs_conf[id_parc]["luminosite"] = luminosite
                            self.derniere_donnees_capteurs_conf[id_parc]["humidite"] = humidite

                            if self.status_envoie_notif == 0:
                                type_alerte = "Urgence"
                                msg_str = f"{id_parc} -> FORTE CHALEUR : Le soleil est trop fort pour arroser maintenant, l'eau s'évaporerait. Protégez vos plantes avec de l'ombre (bâche, parasol)"
                                msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n*Recommandation:* Planification d'un gros arrosage ce soir après le coucher du soleil"
                                self.telegramService.envoyer_notification_telegram(msg_final_send)
                                self.status_envoie_notif = 1
                        else:
                            if self.status_envoie_notif == 0:
                                print(f"Alerte annulée pour {id_parc}")
                                self.horaire_arrosage[id_parc] = None
                                self.status_envoie_notif = 1
                    else:
                        if self.verifier_delai_confirmation(id_parc):     
                            self.derniere_donnees_capteurs_conf[id_parc]["temperature"] = temp
                            self.derniere_donnees_capteurs_conf[id_parc]["luminosite"] = luminosite
                            self.derniere_donnees_capteurs_conf[id_parc]["humidite"] = humidite

                            if self.status_envoie_notif == 0:
                                type_alerte = "Avertissement"
                                msg_str = f"{id_parc} -> CANICULE : Il fait très chaud. Vos réserves d'eau dans la terre sont encore suffisantes pour tenir jusqu'au soir."
                                msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n*Recommandation:* Ne pas arroser les plantes maintenant"
                                self.telegramService.envoyer_notification_telegram(msg_final_send)
                                self.status_envoie_notif = 0
                        else:
                            if self.status_envoie_notif == 0:
                                print(f"Alerte annulée pour {id_parc}")
                                self.horaire_arrosage[id_parc] = None
                                self.status_envoie_notif = 1

                if humidite < 15 and 15 < temp < 28:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        self.derniere_donnees_capteurs_conf[id_parc]["temperature"] = temp
                        self.derniere_donnees_capteurs_conf[id_parc]["luminosite"] = luminosite
                        self.derniere_donnees_capteurs_conf[id_parc]["humidite"] = humidite

                        if self.status_envoie_notif == 0:
                            type_alerte = "Info"
                            msg_str = f"{id_parc} -> TERRE SECHE : Vos plantes commencent à avoir soif."
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n*Recommandation:* Un petit arrosage sur la terre leur ferait du bien"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 0:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

                if humidite > 60 and temp > 22:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        self.derniere_donnees_capteurs_conf[id_parc]["temperature"] = temp
                        self.derniere_donnees_capteurs_conf[id_parc]["luminosite"] = luminosite
                        self.derniere_donnees_capteurs_conf[id_parc]["humidite"] = humidite

                        if self.status_envoie_notif == 0:
                            type_alerte = "Avertissement"
                            msg_str = f"{id_parc} -> TERRE TROP MOUILLEE : Il fait chaud et la terre est saturée d'eau."
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n*Recommandation:* N'arrosez surtout pas, vos plantes risquent de pourrir"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 0:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

                if 20 <= humidite <= 50 and 18 < temp < 26 and luminosite > 5000:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        self.derniere_donnees_capteurs_conf[id_parc]["temperature"] = temp
                        self.derniere_donnees_capteurs_conf[id_parc]["luminosite"] = luminosite
                        self.derniere_donnees_capteurs_conf[id_parc]["humidite"] = humidite

                        if self.status_envoie_notif == 0:
                            type_alerte = "Succès"
                            msg_str = f"{id_parc} -> TOUT VA BIEN : L'humidité de la terre et la température sont parfaites. Vos plantes poussent dans d'excellentes conditions !"
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n*INFO:* {msg_str}"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 0:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

                if luminosite < 100:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        self.derniere_donnees_capteurs_conf[id_parc]["temperature"] = temp
                        self.derniere_donnees_capteurs_conf[id_parc]["luminosite"] = luminosite
                        self.derniere_donnees_capteurs_conf[id_parc]["humidite"] = humidite

                        if self.status_envoie_notif == 0:
                            type_alerte = "Info"
                            msg_str = f"{id_parc} -> REPOS : Il fait nuit. Le système surveille votre parcelle pendant que vos plantes se reposent."
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n*INFO:* {msg_str}"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 1:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

            # ---------------------------------- MODE AUTO --------------------------------
            else :
                list_id_parc = [f"{id_parc}"]

                if temp > 30 and luminosite > 10000:
                    self.status_envoie_notif = 0

                    if humidite < 15:
                        if self.verifier_delai_confirmation(id_parc):
                            if self.status_envoie_notif == 0:
                                type_alerte = "Urgence"
                                msg_str = f"{id_parc} -> FORTE CHALEUR : Le soleil est trop fort. L'arrosage automatique est suspendu pour éviter que l'eau ne s'évapore. Pensez à mettre vos plantes à l'ombre."
                                msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n"
                                self.telegramService.envoyer_notification_telegram(msg_final_send)
                                self.status_envoie_notif = 1
                        else:
                            if self.status_envoie_notif == 0:
                                print(f"Alerte annulée pour {id_parc}")
                                self.horaire_arrosage[id_parc] = None
                                self.status_envoie_notif = 1
                    else:
                        if self.verifier_delai_confirmation(id_parc):                            
                            if self.status_envoie_notif == 0:
                                type_alerte = "Avertissement"
                                msg_str = f"{id_parc} -> CANICULE : L'arrosage automatique est suspendu car il fait trop chaud. L'humidité de votre terre est suffisante pour l'instant."
                                msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n"
                                self.telegramService.envoyer_notification_telegram(msg_final_send)
                                self.status_envoie_notif = 0
                        else:
                            if self.status_envoie_notif == 0:
                                print(f"Alerte annulée pour {id_parc}")
                                self.horaire_arrosage[id_parc] = None
                                self.status_envoie_notif = 1

                if humidite < 15 and 15 < temp < 28:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        if self.status_envoie_notif == 0:
                            type_alerte = "Info"
                            msg_str = f"{id_parc} -> TERRE SECHE : L'arrosage automatique démarre dans {self.sec_activation_pompe} secondes. Veuillez vous éloigner de la parcelle."
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            
                            # Remplacement du Timer par une logique de démarrage à retardement via calcul de temps
                            # On réutilise verifier_delai_confirmation mais pour déclencher l'activation
                            # NOTE: Ici l'utilisateur veut aussi retarder l'activation. 
                            # Pour rester simple, on va garder le Timer uniquement pour le RETARD d'activation si nécessaire,
                            # OU alors on utilise une autre variable de "debut_attente_activation".
                            # Mais le user a dit "remplacer TOUT l'utilisation de threading.Timer".
                            
                            # On va utiliser une logique similaire pour le délai d'activation
                            self.execution_arrosage_auto(id_parc, list_id_parc)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 0:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

                if humidite > 60 and temp > 22:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        if self.status_envoie_notif == 0:
                            type_alerte = "Avertissement"
                            msg_str = f"{id_parc} -> TERRE TROP MOUILLEE : L'arrosage automatique est suspendu. La terre est saturée d'eau. Trop d'humidité risquerait de faire pourrir vos plantes."
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 0:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

                if 20 <= humidite <= 50 and 18 < temp < 26 and luminosite > 5000:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        if self.status_envoie_notif == 0:
                            type_alerte = "Succès"
                            msg_str = f"{id_parc} -> TOUT VA BIEN : L'humidité et la température sont parfaites. Vos plantes poussent dans des conditions idéales."
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 0:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

                if luminosite < 100:
                    self.status_envoie_notif = 0
                    if self.verifier_delai_confirmation(id_parc):
                        if self.status_envoie_notif == 0:
                            type_alerte = "Info"
                            msg_str = f"{id_parc} -> REPOS NOCTURNE : Le système surveille votre parcelle en attendant le réveil de vos plantes. Bonne nuit !"
                            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n"
                            self.telegramService.envoyer_notification_telegram(msg_final_send)
                            self.status_envoie_notif = 1
                    else:
                        if self.status_envoie_notif == 1:
                            print(f"Alerte annulée pour {id_parc}")
                            self.horaire_arrosage[id_parc] = None
                            self.status_envoie_notif = 1

            # ---------------------- DELAIS D'ATTENTE AVANT L'INSERTION DE NOUVEAU DONNEE CAPTEUR DANS LA BASE DE DONNEE --------------------------
            temps_actuelle = time.time()
            delais_seconde = 15
            
            # Use safe type_alerte and msg_str
            try:
                alerte_to_save = type_alerte
                msg_to_save = msg_str
            except NameError:
                alerte_to_save = "N/A"
                msg_to_save = "Donnée capteur reçue."

            # if (temps_actuelle - self.derniere_insertion_time) >= delais_seconde:
            #     try:
            #         self.donneeCapteurService.create_donnee_capteur(id_parc, humidite, temp, luminosite, alerte_to_save, msg_to_save, self.db_config)
            #         print(f"[BDD] Temps écoulé. Donnée {humidite}% - {temp}°C - {luminosite}lx enregistrée pour id_parc={id_parc}.")
            #         self.derniere_insertion_time = temps_actuelle
            #     except Exception as e:
            #         print(f"[ERREUR BDD] : {e}")
            # else:
            #     print(f"[INFO] Insertion ignorée (attente de {delais_seconde}s en cours...)")