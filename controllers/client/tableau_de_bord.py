from flask import Flask, request, jsonify
import time
from datetime import datetime
from flask_apscheduler import APScheduler
import serial 
import serial.tools.list_ports

import threading 

from services.client.tableau_de_bord import ArduinoService
from services.client.planification_service.planification_service import PlanificationService
from services.client.donnee_capteur_service.donnee_capteur_service import DonneeCapteurService
from services.client.led_service.led_service import LedService
from services.client.telegram_service.telegram_service import TelegramService

import json

from config.db_python.db_config import DBConfig
from services.client.donnee_capteur_service.recuperate_data_sensor_mqtt import RecuperateDataSensorMQTT


"""
Cette ligne permet de créer une instance de l'application Flask.
    __name__ est un paramètre obligatoire qui indique le module ou le package actuel.
    Cette instance est celui qui sera utilisé pour traiter les requêtes envoyé par l'utilisateur
    depuis son navigateur et en fonction de l'URL (la route qui est indiqué au niveau de l'URL) 
    une fonction spécifique sera exécutée par l'application Flask
"""
app = Flask(__name__)

# Nous allons utilisé ce dictionnaire pour capturer la valeur spécifiant l'état de la notification actuelle 
# Ce qui va nous permettre de réaliser la logique de fonctionnement suivant : 
# Tant que l'utilisateur se trouve dans la même condition de départ pour l'état d'arrosage (ex : si au départ l'utilisateur était dans la condition
# spécifiant que la condition est mauvaise pour effectuer un arrosage et qu'une notification telegram lui a été déjà envoyé) alors la valeur qui sera
# stocké dans ce dictionnaire pour l'id_parc spécifique permettra de bloquer l'envoie de notification à chaque fois que l'utilisateur fait l'action de cliquer
# sur le bouton "Activer la pompe"
etat_notif_actuelle = {
    "OP_001": None,
    "OP_002": None,
    "OP_003": None,
    "OP_004": None
}

"""
    Elle permet d'instancier l'objet pour permettre l'utilisation de l'APScheluder -> nécessaire pour la planification
    des tâches périodiques
"""
scheduler = APScheduler()

# Instanciation de la classe "DBConfig" pour initialisation de la connexion à la base de donnée
db_config = DBConfig()

planificationService = PlanificationService()
ledService = LedService()
donneeCapteurService = DonneeCapteurService()
telegramService = TelegramService()

# Dictionnaire pour stocker les timers actifs retiré, logique déplacée vers led_service

"""
    Elle permet d'initialiser l'application Flask avec l'APScheduler
    et de démarrer le scheduler.
    En plus claire elle permet d'intégrer l'APScheduler à l'application Flask.
    Pour l'utilisation de la planification des tâches dans notre application Web
    depuis le navigateur à travers le serveur de développement de Flask.
"""
scheduler.init_app(app)

"""
    Pour démarrer le scheduler.
"""
scheduler.start()

arduino_service = ArduinoService()
mqtt_service = RecuperateDataSensorMQTT()

arduino_service.init_arduino(mqtt_service.ip_arduino)

# Cette API permettra de changer le mode d'arrosage de la pompe
@app.route('/mode/arrosage/id_parc=<id_parc>', methods=["POST"])
def mode_arrosage(id_parc):
    mode = request.json["mode"]

    # Avant de mettre à jour la valeur stocké dans le dictionnaire nous allons récupérer l'ancien valeur du mode pour l'id_parc spécifié'
    ancien_mode = mqtt_service.mode_arrosage_dict[id_parc]

    # On met à jour le mode d'arrosage pour la parcelle donnée
    mqtt_service.mode_arrosage_dict[id_parc] = mode

    if ancien_mode != mode:
        msg_final_send = f"*MODE ACTUEL:* {mqtt_service.mode_arrosage_dict[id_parc]}"

        # Envoie du mode actif actuel (MANUEL ou AUTO) sur le bot du telegram
        telegramService.envoyer_notification_telegram(msg_final_send)

    return jsonify({
        "id_parc": id_parc,
        "mode_arrosage_dict": mqtt_service.mode_arrosage_dict[id_parc],
        "status": "success",
        "status_code": 200
    })

# API pour récupérer les données des capteurs ainsi que les messages de notification du choix parcelle de l'utilisateur pour l'afficher dans mon tableau sur streamlit
@app.route('/select/donnee_capteur/id_parc=<id_parc>', methods=["GET"])
def recuperate_data_sensor(id_parc):
    liste_donnee_capteur, status_code = donneeCapteurService.select_donnee_capteur(id_parc, db_config)
    if status_code:
        return jsonify({
            "message": f"Liste des info capteur extrait avec succès pour id_parc={id_parc}",
            "liste_donnee_capteur": liste_donnee_capteur,
            "status": "success",
            "status_code": 200,
        })

# API pour la récupération de l'état initial de la pompe au départ
@app.route('/status/pompe/id_parc=<id_parc>', methods=["GET"])
def status_pompe(id_parc):
    return jsonify({
        "id_parc": id_parc,
        "etat_pompe_dict": mqtt_service.etat_pompe_dict[id_parc],
        "status": "success",
        "status_code": 200
    })

# Récupération des lignes de donnée pour l'historique de planification des prochaines arrosages
@app.route('/historique_arrosage/id_parc=<id_parc>', methods=["GET"])
def recuperation_data_planifie(id_parc):
    list_data_planification, status_code = planificationService.select_planification_arrosage(id_parc, db_config)
    
    # Si la variable "list_data_planification" n'est pas vide alors on va retourner toutes les lignes de données
    # ayant été récupéré. Dans le cas contraire on retourne un tableau vide sur streamlit pour au moins affiché un 
    # tableau vide sur l'interface streamlit
    if status_code == 200:
        if list_data_planification:
            return jsonify({
                "message": f"Liste des prochaines planification extrait avec succès pour id_parc={id_parc}.",
                "status_code": 200,
                "list_data_planification": list_data_planification
            })
        else:
            return jsonify({
                "message": f"Liste des prochaines planification vide",
                "status_code": 200,
                "list_data_planification": list_data_planification
            })

    else:
        return jsonify({
            "message": f"Erreur survenu lors de la tentative de selection de la liste des prochaines planification.",
            "status_code": 400,
            "list_data_planification": list_data_planification
        })

@app.route('/planifier/id_parc=<id_parc>', methods=["POST"])
def planifier(id_parc):
    try:
        data_json = request.get_json()

        date_heure = data_json["date_heure"]
        duree = data_json["duree"]

        # Nous avons passé en argument les fonctions telle que : led_on_thread et led_off_thread.
        # PlanificationService n'a absolument aucune idée de ce qu'est une "Pompe à eau" ou un "Capteur d'humidité". 
        # Son seul travail au monde, c'est de régler des réveils (scheduler.add_job()).
        id_planning, status_code = planificationService.planifier(id_parc, mqtt_service.ip_arduino, scheduler, date_heure, duree, db_config)   
        
        if status_code == 200:
            return jsonify({
                "message": f"Arrosage programmé pour id_parc = {id_parc}",
                "date_heure_planification": datetime.now(),
                "duree": duree,
                "date_execution": date_heure,
                "status": "success",
                "status_code": 200
            })
        
        elif status_code == 409:
            return jsonify({
                "message": f"Arrosage planifié avec l'ID={id_planning} existant déjà dans la base de donnée",
                "status": "erreur doublon",
                "status_code": 409
            })

        else:
            return jsonify({
                "message": f"Erreur survenue lors de la tentative de planification d'une prochaine arrosage pour id_parc={id_parc}",
                "status": "echec",
                "status_code": 400
            })

    except Exception as e :
        print(f"Erreur lors de la tentative de planification technique pour id_parc = {id_parc}")

        return jsonify({
            "message": f"Erreur lors de la tentative de planification technique pour id_parc = {id_parc}",
            "status": "echec",
            "status_code": 400
        })

@app.route('/cancelPlanifier/id_parc=<id_parc>', methods=["DELETE"])
def cancelPlanifier(id_parc):
    data_json = request.get_json()

    id_planning = data_json["id_planning"]

    status_code = planificationService.cancelPlanifier(scheduler, id_planning, db_config)

    # 1ere condition : dans le cas où la planification de la 1ère tâche n'a pas encore été déclenché avant l'annulation en garde le status de la pompe comme il est.
    # 2eme condition : la tâche pour l'activation de la pompe a déjà eu lieu donc c'est le second tâche (qui consiste à annuler la désactivation de la pompe après une certaine durée spécifier par l'utilisateur)
    # qui sera annulé. Cela ne va rien changer aussi par rapport au statut d'activation de la pompe. Donc rien à publier via la communication mqtt
    if status_code == 200:
        return jsonify({
            "message": f"Arrosage annulé pour id_parc = {id_parc}",
            "status": "success",
            "status_code": 200
        })

    else:
        return jsonify({
            "message": f"Tentative d'annulation de planification pour id_parc = {id_parc} échouée",
            "status": "echec",
            "status_code": 400
        })

@app.route('/led/off', methods=["POST"])
def led_off():    
    data_json = request.get_json()
    list_id_parc = data_json["list_id_parc"]

    print(f"Tentative d'extinction de la pompe pour : {list_id_parc}")

    # Attente d'une liste d'id_parc (côté Arduino IDE) du à l'integration du système "Forcer extinction des pompes"
    # Condition pour vérifier qu'il n'y a plus de compte à rebour pour éteindre la pompe stocké dans le dictionnaire "timers_actifs"
    # Pour éviter de l'eteindre une fois de plus, après l'avoir éteint avec le bouton "Eteindre la pompe"
    status_code = ledService.led_off_thread(list_id_parc, mqtt_service.mqtt_client)

    if status_code == 200:
        msg = "Pompe éteinte" if len(list_id_parc) == 1 else "Pompes éteintes"
        return jsonify({
            "list_id_parc": list_id_parc,
            "message": msg,
            "status": "success",
            "status_code": 200
        })
    
    else:
        return jsonify({
            "list_id_parc": list_id_parc,
            "message": f"Erreur de désactivation pour {list_id_parc}",
            "status": "success",
            "status_code": 400
        })

""" 
    Cette ligne permet de définir une route pour l'application Flask.
    C'est une URL qui sera utilisée pour accéder à la fonction index().
    En fonction du nom dont nous avons donné à notre instance que nous allons
    adapter la décoration @app.route(). Ex : si le nom dont nous avons attribué 
    à notre instance est "myapp" alors le nom qui sera aperçu ici sera @myapp.route()
"""
@app.route('/led/on/id_parc=<id_parc>', methods=["POST"])
def led_on(id_parc):
    data_json = request.get_json()
    duree_arrosage = data_json['duree_arrosage_manuelle']

    # On stocke dans une liste l'id_parc avant de l'envoyé à la fonction led_off_service.
    # Etant donné qu'il attend une liste et non une simple valeur. Cela est dû au fait que nous
    # utilisons la fonction led_off_service pour éteindre dans un premier temps une pompe individuellement
    # ou alors dans un second temps toutes les pompes lorsque l'utilisateur clique sur le bouton "Forcer extinction des pompes"
    # sur l'interface streamlit
    list_id_parc = [f"{id_parc}"]

    humidite = mqtt_service.derniere_donnees_capteurs_conf[id_parc]["humidite"]
    temperature = mqtt_service.derniere_donnees_capteurs_conf[id_parc]["temperature"]
    luminosite = mqtt_service.derniere_donnees_capteurs_conf[id_parc]["luminosite"]
  
    # 1. SCENARIO : CANICULE (Chaud + Soleil + PAS de pluie)
    if temperature > 30 and  luminosite > 10000:
        if humidite < 15:
            if not mqtt_service.tentatives_arrosage[id_parc]:

                etat_notif_actuelle[id_parc] = "Urgence"

                type_alerte = "Urgence"
                msg_final_send = f"*Type_alerte:* {type_alerte}\n\nLes conditions sont défavorables pour activer l'arrosage\n\n*FORTE CHALEUR :* risque d'évaporation d'eau\n\nRecliquer sur le bouton *Activer pompe* pour l'activer quand même."

                telegramService.envoyer_notification_telegram(msg_final_send)

                # Pour marquer la tentative d'activation de la pompe par l'utilisateur malgré la situation défavorable des données capteur
                mqtt_service.tentatives_arrosage[id_parc] = True

                return jsonify({
                    "id_parc": id_parc,
                    "message": "Alerte: Urgence",
                    "status": "success",
                    "status_code": 200
                })

        else:
            if not mqtt_service.tentatives_arrosage[id_parc]:

                etat_notif_actuelle[id_parc] = "Avertissement_humidite_sol"

                type_alerte = "Avertissement"
                msg_final_send = f"*Type_alerte:* {type_alerte}\n\nLes conditions sont défavorables pour activer l'arrosage\n\n*LE SOL EST DEJA HUMIDE*\n\nRecliquer sur le bouton *Activer pompe* pour l'activer quand même."

                telegramService.envoyer_notification_telegram(msg_final_send)

                # Pour marquer la tentative d'activation de la pompe par l'utilisateur malgré la situation défavorable des données capteur
                mqtt_service.tentatives_arrosage[id_parc] = True

                return jsonify({
                    "id_parc": id_parc,
                    "message": "Alerte: Avertissement_humidite_sol",
                    "status": "success",
                    "status_code": 200
                })
    # 1. SCENARIO : CANICULE (Chaud + Soleil + PAS de pluie)

    # 2. SCENARIO : RISQUE DE MALADIE (Chaleur + Terre trop trempée)
    elif humidite > 60 and temperature > 22:
        # Ici on ne met pas la condition : etat_notif_actuelle[id_parc] != "Avertissement_maladie" en combinaison (&&) avec la condition qui est déjà là pourquoi ?
        # Puisque le fait d'écrire if not mqtt_service.tentatives_arrosage[id_parc] et if etat_notif_actuelle[id_parc] != "Avertissement_maladie" revient à la même chose 
        # pour bloquer l'envoie une second fois de plus la notification telegram qui avait déjà été envoyé
        if not mqtt_service.tentatives_arrosage[id_parc]:

            etat_notif_actuelle[id_parc] = "Avertissement_maladie"

            type_alerte = "Avertissement"
            msg_final_send = f"*Type_alerte:* {type_alerte}\n\nLes conditions sont défavorables pour activer l'arrosage\n\n*TEMPERATURE TROP ELEVEE ET TERRE TROP MOUILLEE*\n\nRecliquer sur le bouton *Activer pompe* pour l'activer quand même."

            telegramService.envoyer_notification_telegram(msg_final_send)

            # Pour marquer la tentative d'activation de la pompe par l'utilisateur malgré la situation défavorable des données capteur
            mqtt_service.tentatives_arrosage[id_parc] = True

            return jsonify({
                "id_parc": id_parc,
                "message": "Alerte: Avertissement_maladie",
                "status": "success",
                "status_code": 200
            })
    # 2. SCENARIO : RISQUE DE MALADIE (Chaleur + Terre trop trempée)

    # 3. SCENARIO : TERRE SECHE
    elif humidite < 15 and 15 < temperature < 28: 
        # Ici je n'utilise donc pas la même condition que ceux qui sont au-dessus : 
        # if not mqtt_service.tentatives_arrosage[id_parc]: 
        # Puisque nous n'avons pas besoin de reconfirmer une second fois le choix de l'utilisateur (pour en capturer sa première tentative)
        # Une fois que la condition est idéale pour l'arrosage on envoie tout de suite une notification telegram et en même temps, on activera
        # directement la pompe
        if etat_notif_actuelle[id_parc] != "IDEALES":

            etat_notif_actuelle[id_parc] = "IDEALES"

            type_alerte = "Info"
            msg_str = f"{id_parc} -> TERRE SECHE : Vos plantes commencent à avoir soif."
            msg_final_send = f"*Type_alerte:* {type_alerte}\n\n{msg_str}\n\n*Recommandation:* Un petit arrosage sur la terre leur ferait du bien"

            telegramService.envoyer_notification_telegram(msg_final_send)

        # On valide directement l'arrosage sans confirmation. Pour pouvoir entrer directement dans la condition qui permet d'activer la pompe à l'étape
        # qui suit (ci-dessous)
        mqtt_service.tentatives_arrosage[id_parc] = True

    # 4. SCENARIO : METEO NORMALE (Ni canicule, ni maladie, ni sec)
    else :
        # On active la pompe sans faire d'histoire ni de Telegram
        mqtt_service.tentatives_arrosage[id_parc] = True

        # On réinitialise la mémoire au cas où il faisait canicule hier
        etat_notif_actuelle[id_parc] = "NORMAL"

    if mqtt_service.tentatives_arrosage[id_parc] :
        # Nous faisons appel à cette fonction pour pouvoir publier via la communication mqtt le statut de la pompe actuelle. Et ainsi 
        # de l'afficher en temps réelle sur mon interface streamlit
        status_code = ledService.led_on_thread(id_parc, mqtt_service.mqtt_client, duree_arrosage)

        if status_code == 200:
            list_id_parc = [id_parc]

            # Appel de la méthode de désactivation avec un délai (compte à rebours)
            ledService.led_off_thread(list_id_parc, mqtt_service.mqtt_client, duree_arrosage, mqtt_service)

            mqtt_service.tentatives_arrosage[id_parc] = False

            return jsonify({
                "id_parc": id_parc,
                "message": "Pompe Activé",
                "status": "success",
                "status_code": 200
            })
        
        else :
            return jsonify({
                "id_parc": id_parc,
                "message": "Erreur d'activation de la pompe",
                "status": "echec",
                "status_code": 400
            })
        
""" 
    Cette ligne permet de démarrer l'application Flask.
    Elle permet de lancer le serveur de développement de Flask.
    L'argument debug=True permet de lancer le serveur en mode debug.
    Cela permet de recharger automatiquement le serveur lorsqu'un fichier est modifié.

    __name__ == '__main__' permet de vérifier si le fichier est exécuté directement
    et non importé comme module dans un autre fichier.
    Si c'est le cas (où le script est directement exécuté), le serveur sera lancé.
    Dans le cas contraire, le serveur ne sera pas lancé.
"""
if __name__ == '__main__':
    #reloader désactiver pour éviter que les deux instances (1ere instance pour surveiller tout changement dans le code
    #2eme instance s'agissant du serveur Flask pour la gestion des API) ne relance deux fois le code ce qui entraînera
    #l'ouverture du même port par les deux instances ce qui entraînera donc une erreur de port occupé.
    app.run(debug=True, use_reloader=False) 
