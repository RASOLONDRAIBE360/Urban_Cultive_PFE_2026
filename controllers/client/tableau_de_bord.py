from flask import Flask, request, jsonify
import time
from datetime import datetime
from flask_apscheduler import APScheduler
import serial 
import serial.tools.list_ports
from services.client.tableau_de_bord import ArduinoService
import threading
import paho.mqtt.client as mqtt
import json

# Importation des scripts nécessaires pour l'utilisation de l'IA
from config.db_python.db_config import DBConfig
from models.model_agri_ai.CRUD.create_alerte_ai import CreateAlerteAI
from models.model_agri_ai.agri_ai_service import AgriAIService

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

ip_arduino = "192.168.100.125"

delai_conf = 10

# On va utiliser un dictionnaire pour stocker les dernières données des capteurs pour chaque parcelle
# Etant donné que la fonction "on_message" se réexécute à chaque message reçu du broker MQTT
# les valeurs qui ont été récupéré avant par la fonction "on_message" sont perdues. Pour y remédier 
# à ce problème on va stocker dernières données des capteurs pour chaque parcelle
derniere_donnees_capteurs = {
    "OP_001": {
        "temperature": 0.0,
        "luminosite": 0.0,
        "humidite": 0
    },
    "OP_002": {
        "temperature": 0.0,
        "luminosite": 0.0,
        "humidite": 0
    },
    "OP_003": {
        "temperature": 0.0,
        "luminosite": 0.0,
        "humidite": 0
    },
    "OP_004": {
        "temperature": 0.0,
        "luminosite": 0.0,
        "humidite": 0
    }
}

# Dictionnaire pour définir le mode par défaut de l'arrosage
mode_arrosage_dict = {
    "OP_001": "manuel",
    "OP_002": "manuel",
    "OP_003": "manuel",
    "OP_004": "manuel"
}

# Dictionnaire pour gérer l'état des pompes
etat_pompe_dict = {
    "OP_001": "Desactive",
    "OP_002": "Desactive",
    "OP_003": "Desactive",
    "OP_004": "Desactive"
}

# Dictionnaire pour gérer un horaire indépendant pour chaque parcelle
horaire_arrosage = {
    "OP_001": None,
    "OP_002": None,
    "OP_003": None,
    "OP_004": None
}

# Topic pour publication de l'etat des pompes
topic_etat_pompe = "status/pompe"

# ---------------------- CONFIGURATION DE LA CONNEXION A LA BASE DE DONNEES ------------------------
# Instanciation pour la gestion de la connexion à la base de données
db_config = DBConfig()
# ---------------------- CONFIGURATION DE LA CONNEXION A LA BASE DE DONNEES ------------------------

arduino_service.init_arduino(ip_arduino)

# Cette API permettra de changer le mode d'arrosage de la pompe
@app.route('/mode/arrosage/id_parc=<id_parc>', methods=["POST"])
def mode_arrosage(id_parc):
    mode = request.json["mode"]

    # On met à jour le mode d'arrosage pour la parcelle donnée
    mode_arrosage_dict[id_parc] = mode

    return jsonify({
        "id_parc": id_parc,
        "mode_arrosage_dict": mode_arrosage_dict[id_parc],
        "status": "success"
    })

@app.route('/status/pompe/id_parc=<id_parc>', methods=["GET"])
def status_pompe(id_parc):
    return jsonify({
        "id_parc": id_parc,
        "etat_pompe_dict": etat_pompe_dict[id_parc],
        "status": "success"
    })

@app.route('/led/off/id_parc=<id_parc>', methods=["POST"])
def led_off(id_parc):
    arduino_service.led_off_service(ip_arduino, id_parc)
    etat_pompe_dict[id_parc] = "Desactive"

    return jsonify({
        "id_parc": id_parc,
        "message": "LED eteinte",
        "status": "success"
    })

""" Cette ligne permet de définir une route pour l'application Flask.
    C'est une URL qui sera utilisée pour accéder à la fonction index().
    En fonction du nom dont nous avons donné à notre instance que nous allons
    adapter la décoration @app.route(). Ex : si le nom dont nous avons attribué 
    à notre instance est "myapp" alors le nom qui sera aperçu ici sera @myapp.route()
"""
@app.route('/led/on/id_parc=<id_parc>', methods=["POST"])
def led_on(id_parc):
    arduino_service.led_on_service(ip_arduino, id_parc)
    etat_pompe_dict[id_parc] = "Active"

    return jsonify({
        "id_parc": id_parc,
        "message": "LED allumée",
        "status": "success"
    })

def led_off_thread(ip_arduino, id_parc):
    arduino_service.led_off_service(ip_arduino, id_parc)
    etat_pompe_dict[id_parc] = "Desactive"
    mqtt_client.publish (f"{topic_etat_pompe}/{id_parc}", f"{etat_pompe_dict[id_parc]}")

def led_on_thread(ip_arduino, id_parc):
    arduino_service.led_on_service(ip_arduino, id_parc)
    etat_pompe_dict[id_parc] = "Active"
    mqtt_client.publish(f"{topic_etat_pompe}/{id_parc}", f"{etat_pompe_dict[id_parc]}")
    
# Fonction appelée lorsque le client MQTT se connecte au broker MQTT
def on_connect(client, userdata, flags, rc):
    print("Connecté au broker MQTT avec le code : " + str(rc))

    # "#" -> nous permet de s'abonner à tous les topics qui commencent par "data/temperature/"
    client.subscribe(f"data/temperature/#")
    client.subscribe(f"data/luminosite/#")
    client.subscribe(f"data/humidite/#")

# Fonction appelée à chaque message reçu du broker MQTT
def on_message(client, userdata, msg):
    topic = msg.topic

    # On récupère l'id_parc à partir du topic

    # Dans le topic "data/temperature/OP_001", l'id_parc est à la 3ème position (index 2)

    # On utilise split("/") pour séparer le topic en plusieurs parties
    # On utilise [2] pour récupérer la 3ème partie (l'id_parc)

    # liste = ["data", "temperature", "OP_001"]
    # id_parc = liste[2]

    if len(topic.split("/")) == 3:
        id_parc = topic.split("/")[2]

        # Extraction du donnée type capteur
        cle = topic.split("/")[1]

        if mode_arrosage_dict[id_parc] == "manuel":
            return
        
        else :
            # On charge/récupère les données envoyer par le capteur en format JSON puis on le décode
            msg_json = json.loads(msg.payload.decode("utf-8"))

            # On met à jour la valeur du capteur qui vient d'être envoyé par l'Arduino
            derniere_donnees_capteurs[id_parc][cle] = float(msg_json[cle])

            # Etant donné que les données de capteur ne seront pas envoyés en même temps
            # on va utiliser la valeur des capteurs qui sont déjà stockés dans le dictionnaire 
            # pour la condition qui suit
            temp = derniere_donnees_capteurs[id_parc]["temperature"]
            luminosite = derniere_donnees_capteurs[id_parc]["luminosite"]
            humidite = derniere_donnees_capteurs[id_parc]["humidite"]

            # Activation automatique lorsque : "LE SOL EST SEC + LA TEMPERATURE EST ELEVE + LA LUMINOSITE EST FAIBLE"
            # La luminosite doit être faible pour éviter l'évaporation de l'eau en contact avec le soleil
            if humidite < 40 and temp > 35 and luminosite < 15000:
                if horaire_arrosage[id_parc] is None:
                    horaire_arrosage[id_parc] = time.time()
                else :
                    temps_ecoule = time.time() - horaire_arrosage[id_parc]
                    if temps_ecoule > delai_conf:
                        print(f"Capteur DHT11 / Capteur raindrop sensor -> Alerte confirmée pour {id_parc} : {temp} °C / {humidite} %. Allumage automatique de la pompe pour 20s")
                        
                        led_on_thread(ip_arduino, id_parc)
                        etat_pompe_dict[id_parc] = "Active"

                        # Appel de la fonction pour éteindre la pompe après 20s
                        threading.Timer(5, led_off_thread, args=[ip_arduino, id_parc]).start()

                        # Réinitialisation de l'horaire de déclenchement
                        horaire_arrosage[id_parc] = None

            # Activation automatique lorsque : "LA TEMPERATURE EST ELEVE + LA LUMINOSITE EST FAIBLE"
            elif humidite > 60:#luminosite < 15000 and temp > 30:
                if horaire_arrosage[id_parc] is None:
                    horaire_arrosage[id_parc] = time.time()
                else :
                    temps_ecoule = time.time() - horaire_arrosage[id_parc]
                    if temps_ecoule > delai_conf:
                        print(f"Capteur DHT11 / Capteur BH1750 / Capteur raindrop sensor -> Alerte confirmée pour {id_parc} : {temp} °C / {luminosite} lx / {humidite} %. Allumage automatique de la pompe pour 20s")
                        
                        # Etant donnee que l'API route pour gérer l'allumage de la pompe return un document JSON
                        # nous ne pourrons pas nous en servir ici. Puisqu'elle est fait pour être utiliser
                        # dans le cadre d'une requête HTTP et non dans le cadre d'une exécution de thread (application qui tourne en arrière plan)
                        
                        # Pour cela nous allons faire appel au service directement qui gère l'allumage de la pompe sans passer par l'API route
                        led_on_thread(ip_arduino, id_parc)
                        etat_pompe_dict[id_parc] = "Active"

                        # Appel de la fonction pour éteindre la pompe après 20s
                        threading.Timer(5, led_off_thread, args=[ip_arduino, id_parc]).start()

                        # Réinitialisation de l'horaire de déclenchement
                        horaire_arrosage[id_parc] = None

            else:
                print(f"Alerte annulée pour {id_parc}")
                horaire_arrosage[id_parc] = None

# On lance le client MQTT en arrière plan du serveur Flask
mqtt_client = mqtt.Client()
mqtt_client.on_connect = on_connect
mqtt_client.on_message = on_message
mqtt_client.connect("192.168.100.117", 1883)

# Necessaire pour maintenir la connexion au broker MQTT
mqtt_client.loop_start()

@app.route('/planifier/id_parc=<id_parc>', methods=["POST"])
def planifier(id_parc):
    arduino_service.planifier(id_parc, ip_arduino, scheduler)    
    return jsonify({
        "message": f"Arrosage programmé pour id_parc = {id_parc}",
        "date_heure_planification": datetime.now(),
        "duree": duree,
        "date_execution": date_heure,
        "status": "success"
    })

@app.route('/cancelPlanifier/id_parc=<id_parc>', methods=["DELETE"])
def cancelPlanifier(id_parc):
    response =arduino_service.cancelPlanifier(id_parc, scheduler)
    data = response.json()

    if data["status"] == 200:
        return jsonify({
            "message": f"Arrosage annulé pour id_parc = {id_parc}",
            "status": 200
        })
    else:
        return jsonify({
            "message": f"Arrosage non annulé pour id_parc = {id_parc}",
            "status": 400
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
