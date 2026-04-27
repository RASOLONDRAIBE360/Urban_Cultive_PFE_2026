import requests
import json
import threading

# Dictionnaires pour mémoriser les actions programmées et éviter les conflits
timers_actifs = {
    "OP_001": None,
    "OP_002": None,
    "OP_003": None,
    "OP_004": None
}

class LedService:

######################## SERVICE #########################
    def led_on_service(self, mqtt_client, id_parc):
        message_commande = {
            "commande": "ON",
            "list_id_parc": [id_parc]
        }

        # Nécessaire pour convertir mon dictionnaire "list_id_parc" en document json qui sera ensuite renvoyé vers l'arduino 
        # via la communication MQTT pour activer la pompe d'arrosage
        payload = json.dumps(message_commande)

        mqtt_client.publish("/led/on", payload)

        # On renvoie 200 "aveuglement" ici car on sait que le message est parti.
        # La VRAIE confirmation viendra plus tard via MQTT dans la fonction on_message (dans le service : recuperate_data_sensor_mqtt.py)
        return 200

    def led_off_service(self, mqtt_client, list_id_parc):
        message_commande = {
            "commande": "OFF",
            "list_id_parc": list_id_parc
        }

        payload = json.dumps(message_commande)

        mqtt_client.publish("/led/off", payload)

        return 200
######################## SERVICE #########################

######################## THREAD ##########################
# Les fonctions thread sont appelés pour justement envoyé en temps réelle via la communication MQTT le status d'activation des pompes
# Après avoir réaliser un action spécifique (activation ou extinction de la pompe)
# Ici la fonction led_off_thread ou led_on_thread ne prend pas comme paramètre "self" puisque ce ne sont pas des fonctions qui sont propres
# à une classe
    def led_off_thread(self, list_id_parc, mqtt_client):
        # On part du principe que l'action va échouer. Pour lui donner une valeur défaut.
        # Afin d'éviter toute éventuelle erreur lié à une variable non défini (au niveau du return status_code)
        status_code = 400

        # 1. Annuler les timers existants (arrêt manuel prioritaire)
        for id_parc in list_id_parc:
            # On vérifie si la parcelle a un timer en cours (non None)
            if timers_actifs.get(id_parc) is not None:
                try:
                    timers_actifs[id_parc].cancel()
                    del timers_actifs[id_parc]
                    print(f"[TIMER] Timer annulé pour {id_parc}")
                
                except Exception as e:
                    print(f"[TIMER] Erreur lors de l'annulation pour {id_parc}: {e}")
                
                status_code = self.led_off_service(mqtt_client, list_id_parc)

        return status_code

    def led_on_thread(self, id_parc, mqtt_client, duree_arrosage):
        status_code = 400
        
        self.led_on_service(mqtt_client, id_parc)
        def exec_off():
            list_id_parc = [id_parc]
            self.led_off_service(mqtt_client, list_id_parc)

        timer = threading.Timer(duree_arrosage, exec_off)
        timer.start()
        timers_actifs[id_parc] = timer
        status_code = 200

        return status_code
######################## THREAD ##########################