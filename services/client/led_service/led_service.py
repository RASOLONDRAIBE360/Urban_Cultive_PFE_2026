import requests
import json
import threading

# Dictionnaires pour mémoriser les actions programmées et éviter les conflits
timers_pompes_off = {}

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
    def led_off_thread(self, list_id_parc, mqtt_client, duree_arrosage=0, mqtt_service=None):
        # 1. Annuler les timers existants (arrêt manuel prioritaire)
        for id_parc in list_id_parc:
            if id_parc in timers_pompes_off:
                timers_pompes_off[id_parc].cancel()
                del timers_pompes_off[id_parc]
                print(f"[TIMER] Timer annulé pour {id_parc}")
            
                status_code = self.led_off_service(mqtt_client, list_id_parc)

        #for id_parc in list_id_parc:
        #    if status_code == 200:
                # Désormais la logique de mise à jour pour l'état de la pompe d'arrosage serait la suivante :
                # L'interface ne changera de couleur (pour indiquer l'état actuelle de la pompe : si activé ou désactivé)
                # que lorsque l'Arduino aura crié "C'est bon, j'ai fini !"
                # La gestion de la mise à jour sera donc laisser à la fonction "on_message" (qui se situe dans le service : recuperate_data_sensor_mqtt.py)
        #        etat_pompe_dict[id_parc] = "Desactive"
        #        mqtt_client.publish (f"{topic_etat_pompe}/{id_parc}", f"{etat_pompe_dict[id_parc]}")

        #    else :
                # En cas d'erreur lors de la tentative d'éteignage de la pompe nous renvoyant le status de la pompe
                # comme telle sans mise à jour
        #        print(f"[ERREUR] Échec extinction de la pompe pour {id_parc} (Status: {status_code})")
        #        mqtt_client.publish (f"{topic_etat_pompe}/{id_parc}", f"{etat_pompe_dict[id_parc]}")

        return status_code

    def led_on_thread(self, id_parc, mqtt_client, duree_arrosage):
        self.led_on_service(mqtt_client, id_parc)
        def exec_off():
            list_id_parc = [id_parc]
            self.led_off_service(mqtt_client, list_id_parc)

        timer = threading.Timer(duree_arrosage, exec_off)
        timer.start()
        status_code = 200
        
        #if status_code == 200:
        #    etat_pompe_dict[id_parc] = "Active"
        #    mqtt_client.publish(f"{topic_etat_pompe}/{id_parc}", f"{etat_pompe_dict[id_parc]}")

        #else :
        #    print(f"[ERREUR] Échec activation de la pompe pour {id_parc} (Status: {status_code})")
        #    mqtt_client.publish (f"{topic_etat_pompe}/{id_parc}", f"{etat_pompe_dict[id_parc]}")

        return status_code
######################## THREAD ##########################