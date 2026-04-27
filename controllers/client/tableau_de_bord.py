from flask import Flask, request, jsonify
from datetime import datetime
from flask_apscheduler import APScheduler

from models.CRUD.client.dashboard_iot.seuillage.seuil_capteur import SeuilCapteurModel

from services.client.tableau_de_bord import ArduinoService
from services.client.planification_service.planification_service import PlanificationService
from services.client.donnee_capteur_service.donnee_capteur_service import DonneeCapteurService
from services.client.led_service.led_service import LedService
from services.client.telegram_service.telegram_service import TelegramService
from services.client.seuillage.seuillage_service import SeuillageService

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

# API pour récupérer les données des capteurs ainsi que les messages de notification du choix parcelle 
# de l'utilisateur pour l'afficher dans mon tableau sur streamlit
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
    list_data_planification, status_code = planificationService.select_liste_planification_arrosage(id_parc, db_config)
    
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

        intervalle_repetition = data_json.get("intervalle_sec", 0)
        nombre_freq = data_json.get("nombre_freq", 1)

        # Nous avons passé en argument les fonctions telle que : led_on_thread et led_off_thread.
        # PlanificationService n'a absolument aucune idée de ce qu'est une "Pompe à eau" ou un "Capteur d'humidité". 
        # Son seul travail au monde, c'est de régler des réveils (scheduler.add_job()).
        id_planning, status_code = planificationService.planifier(
            id_parc, 
            scheduler, 
            date_heure, 
            duree, 
            intervalle_repetition,
            nombre_freq,
            db_config
            )   
        
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

    status_code = ledService.led_on_thread(id_parc, mqtt_service.mqtt_client, duree_arrosage)

    if status_code == 200:

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

@app.route('/register/seuillage', methods=["POST"])
def modif_valeur_seuillage():
    data_json = request.get_json()

    id_parc = data_json.get("id_parc", "")
    temp_seuil = data_json.get("temp_seuil", 0)
    humidite_seuil = data_json.get("humidite_seuil", 0)

    seuilCapteurModel = SeuilCapteurModel(
        id_parc,
        temp_seuil,
        humidite_seuil
    )

    seuillageService = SeuillageService(db_config)
    status_code = seuillageService.updateSeuilData(seuilCapteurModel)

    if status_code == 200:
        print("Enregistrement du Seuillage Réussi !")

        # Une fois que la mise à jour des valeurs de seuil effectué avec succès en remet les valeurs de l'id_parc spécifique
        # stocké dans le dictionnaire à son état initial self.dernier_seuil_enregistre[id_parc] = [None, None]. Pour permettre 
        # la mise à jour des valeurs de seuil stocké en cache (dans le dictionnaire) pour l'id_parc spécifique
        mqtt_service.dernier_seuil_enregistre[id_parc] = [None, None]
        
        return jsonify({
            "message": "Enregistrement du Seuillage Réussi !",
            "status_code": 200,
            "status": "succes"
        })

    else:
        print("Erreur survenu lors de la tentative d'enregistrement du seuillage !")
        return jsonify({
            "message": "Erreur survenu lors de la tentative d'enregistrement du seuillage !",
            "status_code": 400,
            "status": "echec"
        })

@app.route('/recuperate/seuillage/id_parc=<id_parc>', methods=["GET"])
def recuperate_seuil_data(id_parc):
    seuillageService = SeuillageService(
        db_config
    )

    liste_seuil_data, status_code = seuillageService.selectSeuilData(
        id_parc
    )

    if status_code == 200:
        print(f"Recuperation donnee de seuil pour id_parc = {id_parc}")
        return jsonify({
            "message": f"Recuperation donnee de seuil pour id_parc = {id_parc}",
            "liste_seuil_data": liste_seuil_data,
            "status_code": 200,
            "status": "succes"
        })

    else:
        print(f"Erreur survenu lors de la tentative de recuperation donnee de seuil pour id_parc = {id_parc}")
        return jsonify({
            "message": f"Erreur survenu lors de la tentative de recuperation donnee de seuil pour id_parc = {id_parc}",
            "liste_seuil_data": liste_seuil_data,
            "status_code": 400,
            "status": "succes"
        })

#@app.route('/update_info_planification', methods=["PUT"])
#def update_info_planification():
#    data_json = request.get_json()

#    id_planning = data_json["id_planning"]
#    nouveau_moment_programme = data_json["nouveau_moment_programme"]
#    nouveau_duree_arrosage = data_json["nouveau_duree_arrosage"]
#    nouveau_intervalle_rep = data_json["nouveau_intervalle_rep"]
#    nouveau_nombre_freq = data_json["nouveau_nombre_freq"]

#    planificationService.update_planifier(scheduler, id)

@app.route('/update_status_planification_activation', methods=["PUT"])
def update_status_planification_activation():
    data_json = request.get_json()

    id_parc = data_json["id_parc"]
    id_planning = data_json["id_planning"]
    status = data_json["status"]

    status_code = planificationService.update_status_planification_arrosage(
                                            scheduler=scheduler, 
                                            id_parc=id_parc, 
                                            id_planning=id_planning, 
                                            status=status, 
                                            db_config=db_config
                                        )

    if status_code == 200:
        return jsonify({
            "message": f"Désactivation planification pour id_planning = {id_planning} !",
            "status_code": 200
        })

    elif status_code == 400:
        return jsonify({
            "message": f"Echec de désactivation de l'arrosage planifié pour id_planning = {id_planning} !",
            "status_code": 400
        })

@app.route('/update_mode_systeme', methods=["PUT"])
def update_mode_systeme():
    data_json = request.get_json()
    mode_systeme_actuel = data_json["mode_systeme_actuel"]
    id_parc = data_json["id_parc"]

    mode_systeme_update, status_code = planificationService.update_mode(id_parc, mode_systeme_actuel, db_config)
    
    if status_code == 200:
        # Pour synchroniser le programmateur (pour les arrosages planifier) avec le nouveau mode 
        planificationService.gestion_job_planification_arrosage(scheduler, id_parc, mode_systeme_update, db_config)

        msg_final_send = f"<b>MODE ACTUEL:</b> {mode_systeme_update}"

        # Envoie du mode actif actuel (MANUEL ou AUTO) sur le bot du telegram
        telegramService.envoyer_notification_telegram(id_parc, db_config, msg_final_send)

        return jsonify({
            "message": "Mise à jour mode du système !",
            "mode_systeme_update": mode_systeme_update,
            "status_code": 200
        })

    elif status_code == 400:
        msg_final_send = (
            "<b>❌ ERREUR :</b><br>"
            "<i>Echec de mise à jour du mode actuel du système !</i>"
        )

        # Envoie du mode actif actuel (MANUEL ou AUTO) sur le bot du telegram
        telegramService.envoyer_notification_telegram(id_parc, db_config, msg_final_send)

        return jsonify({
            "message": "Echec de mise à jour du mode actuel du système !",
            "status_code": 400
        })

@app.route('/select_mode_systeme/id_parc=<id_parc>', methods=["GET"])
def select_mode_systeme(id_parc):
    mode_systeme_actuel, status_code = planificationService.select_mode(id_parc, db_config)

    if status_code == 200:
        return jsonify({
            "message": "Récupération du mode actuel du système !",
            "mode_systeme_actuel": mode_systeme_actuel,
            "status_code": 200
        })

    elif status_code == 400:
        return jsonify({
            "message": "Echec de la récupération du mode actuel du système !",
            "mode_systeme_actuel": mode_systeme_actuel,
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
