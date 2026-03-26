import time
import requests
from datetime import datetime
from flask import request
from flask_apscheduler import APScheduler
import serial 
import serial.tools.list_ports

class ArduinoService:
    def init_arduino(self, ip_ou_port):
        if "." in ip_ou_port:
            print(f"--- Mode Wi-Fi détecté ---")
            try:
                print("Envoie de requête pour tester si l'arduino est atteignable ou pas ...")
                #Pour tester si l'arduino est capable de répondre dans un délai de 4 secondes maximum
                response = requests.get(f"http://{ip_ou_port}/led/on/id_parc=OP_001", timeout=4)
                if response.status_code == 200:
                    print("Arduino atteignable.")
                    return response
            except:
                print("Arduino non atteignable.")
                return None

    def led_on_service(self, ip_arduino, id_parc):
        response = requests.get(f"http://{ip_arduino}/led/on?id_parc={id_parc}")
        if response.status_code == 200:
            print(f"Success !!! La LED {id_parc} est allumée.")
            print(f"Réponse Arduino: {response.text}")
        elif response.status_code == 400:
            print(f"Error {response.status_code} !!!")
            print(f"Détails: {response.text}")

    def led_off_service(self, ip_arduino, id_parc):
        response = requests.get(f"http://{ip_arduino}/led/off?id_parc={id_parc}")
        if response.status_code == 200:
            print(f"Success !!! La LED {id_parc} est éteinte.")
            print(f"Réponse Arduino: {response.text}")
        elif response.status_code == 400:
            print(f"Error {response.status_code} !!!")
            print(f"Détails: {response.text}")

    def planifier(self, id_parc, ip_arduino, scheduler):
        #Utiliser pour récupérer les données en format json envoyé par l'utilisateur depuis le navigateur
        data = request.get_json()

        date_str = data['date_heure']
        #Conversion de donnée envoyé par le navigateur en format chaîne de caractère en entier
        duree = int(data['duree'])

        ###############################  DEBUT  ################################################################
        """
            Le navigateur a envoyé la date et l'heure saisie par l'utilisateur en format chaîne de caractère. 
            C'est pourquoi il nous est nécessaire de convertir d'abord la chaîne en heure.

            -> heure = datetime.strptime(heure_str, '%H:%M')
        """
        date_heure = datetime.strptime(date_str, '%Y-%m-%d %H:%M')
        ###############################   FIN   ################################################################

        ###############################  DEBUT  ################################################################
        """
            On utilise ici 'datetime.now' juste pour récupérer la date d'aujourd'hui. Afin d'éviter l'utilisation 
            de la date par défaut : 1er Janvier 1900 (c'est la date qui nous est associé à notre objet datetime
            dans le nom de la variable qui le stocke est 'heure' ci-dessus)

            -> date_auj = datetime.now()
        """
        ###############################   FIN   ################################################################

        ###############################  DEBUT  ################################################################
        """
            Pour convertir les données pour heure, minute, seconde, microseconde de "date_auj" récupérer ci-dessus
            en donnée exacte. Sur lequel APScheduler pourrait travailler pour la planification du prochain tâche.

            -> date_exact = date_auj.replace(hour=heure.hour,
                                    minute=heure.minute,
                                    second=0,
                                    microsecond=0)
        """
        ###############################   FIN   ################################################################
        
        ###############################  DEBUT  ################################################################
        """
            Ici nous utilisons APScheduler pour ajouter une tâche planifiée.
        """

        scheduler.add_job(
            id = f"start_{id_parc}",
            func = lambda: self.led_on_service(ip_arduino, id_parc), #Avec "lambda" nous retardons l'exécution de notre fonction en fonction du délais de planification dont nous avons indiqué ci-dessus à l'APScheduler. Afin d'éviter une exécution immédiate de la fonction 
            trigger = "date", #Nous spécifions ici le type de déclencheur en "date" pour dire que la tâche sera exécutée à une date et une heure précise
            run_date = date_heure, #Nous spécifions ici la date et l'heure exacte à laquelle la tâche sera exécutée
        )
        ###############################   FIN   ################################################################
        
        ###############################  DEBUT  ################################################################
        scheduler.add_job(
            id = f"stop_{id_parc}",
            func = lambda: self.led_off_service(ip_arduino, id_parc), #Avec "lambda" nous retardons l'exécution de notre fonction en fonction du délais de planification dont nous avons indiqué ci-dessus à l'APScheduler. Afin d'éviter une exécution immédiate de la fonction 
            trigger = "date", #Nous spécifions ici le type de déclencheur en "date" pour dire que la tâche sera exécutée à une date et une heure précise
            run_date = date_heure + timedelta(minutes=duree), #Nous spécifions ici la date et l'heure exacte à laquelle la tâche sera exécutée
        )
        ###############################   FIN   ################################################################

    def cancelPlanifier(self, id_parc, scheduler):
        start_job = scheduler.get_job(f"start_{id_parc}")
        stop_job = scheduler.get_job(f"stop_{id_parc}")

        if start_job or stop_job:
            if start_job:
                scheduler.remove_job(f"start_{id_parc}")
            if stop_job:
                scheduler.remove_job(f"stop_{id_parc}")
            
            return jsonify({
                "status": 200
            })

        else:
            return jsonify({
                "status": 400
            })



