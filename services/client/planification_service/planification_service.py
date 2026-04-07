from datetime import datetime
import time

from models.CRUD.client.dashboard_iot.planification_arrosage.create_planification import PlanificationModel

from services.client.led_service.led_service import LedService
from services.client.donnee_capteur_service.recuperate_data_sensor_mqtt import RecuperateDataSensorMQTT

class PlanificationService:
    ledService = LedService()
    mqtt_service = RecuperateDataSensorMQTT()

    def fonction_led_programmer(self,ip_arduino, id_parc, duree_arrosage):
        self.ledService.led_on_thread(id_parc, self.mqtt_service.mqtt_client)

        list_id_parc = [f"{id_parc}"]
        
        # Activer le Timer d'extinction automatique
        self.ledService.led_off_thread(list_id_parc, self.mqtt_service.mqtt_client, duree_arrosage, self.mqtt_service)

        print(f"[SYSTEM] Planification démarrée pour {id_parc} (Durée: {duree_arrosage}s)")

    def planifier(self, id_parc, ip_arduino, scheduler, date_str, duree, db_config):
        list_id_parc = [f"{id_parc}"]

        # Le bloc try...except utilisé ici pour capturer et lever une exception dans le cas où une erreur survient lors d'une tentative de planification de tâche
        try:
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
            
            verif_job_id = f"start_{id_parc}_{date_heure}"

            # Vérification de l'existance de  l'alarme dans le chronomètre
            if scheduler.get_job(verif_job_id):
                print(f"[ALERTE] Un arrosage avec ID = {verif_job_id} est déjà planifié à la même heure")
                return verif_job_id, 409
            
            else:
                """
                    Ici nous utilisons APScheduler pour ajouter une tâche planifiée.
                """
                # Déclenchement de l'allumage défini selon la valeur -> date_heure ayant été planifié par l'utilisateur depuis l'interface streamlit
                job_start = scheduler.add_job(
                    id = f"start_{id_parc}_{date_heure}",
                    func = lambda: self.fonction_led_programmer(ip_arduino, id_parc, duree), #Avec "lambda" nous retardons l'exécution de notre fonction en fonction du délais de planification dont nous avons indiqué ci-dessus à l'APScheduler. Afin d'éviter une exécution immédiate de la fonction 
                    trigger = "date", #Nous spécifions ici le type de déclencheur en "date" pour dire que la tâche sera exécutée à une date et une heure précise
                    run_date = date_heure, #Nous spécifions ici la date et l'heure exacte à laquelle la tâche sera exécutée
                )

                # ---------------- INSERTION DANS LA BASE DE DONNEE -----------------
                # Dans le cas où la planification a bien réussi alors la fonction "add_job" nous renvoie un objet "Job". A partir de cette objet nous allons en extraire son Id pour faire une vérification que la planification a bien eu lieu 
                if job_start.id:
                    print(f"Succès matériel : Job {job_start.id} planifié.")

                    date_arrosage = date_heure.strftime("%Y-%m-%d")
                    heure_arrosage = date_heure.strftime("%H:%M")

                    status_code = self.create_planification_arrosage(job_start.id, id_parc, duree, date_arrosage, heure_arrosage, db_config)

                    if status_code == 200:
                        print(f"Insertion de {job_start.id} réussi dans la base de donnée pour id_parc={id_parc}")
                        return job_start.id, 200
                                
                    else:
                        # Dans le cas où l'insertion à la base de donnée échoue alors nous allons annuler le job pour rester cohérent
                        scheduler.remove_job(job_start.id)
                        print(f"Erreur survenue lors de la tentative d'insertion de {job_start.id} dans la base de donnée pour id_parc={id_parc}")
                        return job_start.id, 400
                # ---------------- INSERTION DANS LA BASE DE DONNEE -----------------
                ###############################   FIN   ################################################################

        except Exception as e:
            print(f"[ERREUR SCHEDULER] : {e}")
            return None, 400 # Valeur retourner par la fonction en cas d'échec de la planification

    # Nous pouvons annuler l'arrosage si et seulement si elle n'a pas encore eu lieu. Dans le cas, où la l'arrosage c'est déjà activé nous ne pouvons l'arrêter qu'en cliquant sur le boutton "Eteindre la pompe" (uniquement. Aucune autre choix possible)
    def cancelPlanifier(self, scheduler, id_planning, db_config):
        try:
            start_job = scheduler.get_job(id_planning)

            if start_job:
                scheduler.remove_job(id_planning)
                print(f"Job avec id_planning={id_planning} supprimé avec succès")
            else:
                print(f"Job avec id_planning={id_planning} introuvable")

            status_code = self.delete_planification_arrosage(id_planning, db_config)

            if status_code == 200:
                print(f"Annulation de la planification pour id_planning={id_planning} réussi")
                return 200

            else:
                print(f"Echec d'annulation de la planification pour id_planning={id_planning}")
                return 400

        except Exception as e:
            print(f"[ERREUR ANNULATION SCHEDULER] : {e}")
            return 400
    
    def delete_planification_arrosage(self, id_plann, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                sql_verif_data = f"SELECT * FROM planification_arrosage WHERE Id_planning = %s"
                valeurs_verif_data = [id_plann]

                cursor.execute(sql_verif_data, valeurs_verif_data)
                existe_deja = cursor.fetchone()

                if existe_deja:
                    sql_delete = f"DELETE FROM planification_arrosage WHERE Id_planning = %s"
                    valeurs = [id_plann]

                    cursor.execute(sql_delete, valeurs)
                    conn.commit()
                    return 200
                
                else:
                    return 400
            
            finally:
                db_config.close()
        
        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 400

    def create_planification_arrosage(self, id_plann, id_parc, duree_arr, date_arr, heure_arr, db_config):
        # Instanciation de la classe "Model" + initialisation des champs de ma table "planification_arrosage" stocké en base de donnée
        nouvelle_planification_arrosage = PlanificationModel(
            id_planning = id_plann,
            id_parc = id_parc,
            duree_arrosage = duree_arr,
            date_arrosage = date_arr,
            heure_arrosage = heure_arr
        )

        # Récupération des attributs retournés par la fonction "connect" (spécifié par l'objet -> "db_config")
        # Nécessaire pour ouvrir la connexion à la base de donnée 
        # conn -> qui servira d'ouvrir et fermer la session de connexion à la base de donnée
        # cursor -> pour envoyer et manipuler les requêtes nécessaire pour interagir avec la base de donnée. Pour réaliser des actions telle que
        # insertion, modification, suppression des données dans la base de donnée
        conn, cursor = db_config.connect()

        # La condition suivante indique que : l'insertion de nouvelle donnée dans la base de donnée ne sera possible que si et seulement si la connexion 
        # à la base de donnée c'est bien établie comme il se doit. Dans le contraire il sera retourner un False (pour indiquer "Erreur survenu lors de la tentative
        # de connexion à la base de donnée")
        if conn and cursor:
            try:
                sql_verif_data = f"SELECT * FROM {nouvelle_planification_arrosage.__tablename__} WHERE Id_planning = %s"
                valeurs_verif_data = [nouvelle_planification_arrosage.Id_planning]

                cursor.execute(sql_verif_data, valeurs_verif_data)
                existe_deja = cursor.fetchone()

                if not existe_deja:
                    sql_create = f"INSERT INTO {nouvelle_planification_arrosage.__tablename__} (Id_planning, Id_parc, Duree_arrosage, Date_arrosage, Heure_arrosage) VALUES (%s, %s, %s, %s, %s)"

                    # Après que nous ayons initialisés la valeur des champs pour la table "planification_arrosage" via le constructeur. Nous allons les récupérés à partir de l'instance de la classe nommée
                    # nouvelle_planification_arrosage
                    valeurs = [nouvelle_planification_arrosage.Id_planning, nouvelle_planification_arrosage.Id_parc, nouvelle_planification_arrosage.Duree_arrosage, nouvelle_planification_arrosage.Date_arrosage, nouvelle_planification_arrosage.Heure_arrosage]

                    # Envoie et exécution de la requête pour l'insertion de nouvelle donnée dans la base de donnée
                    cursor.execute(sql_create, valeurs)

                    # Confirmation sans retour (définitive) de l'exécution de la requête pour l'insertion de nouvelle donnée dans la table stocké en base de donnée
                    conn.commit()
                    return 200
                else:
                    return 409
                    
            finally:
                # Fermeture/clôture de la connexion avec la base de donnée pour l'objet d'instance nommée "db_config"
                db_config.close()

        else :
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 400

    def select_planification_arrosage(self, id_parc, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:

                # Je cite les noms des colonnes ici pour respecter le même ordre lors de la récupération et affichage des données sur mon interface streamlit
                sql_select = "SELECT Id_planning, Id_parc, Duree_arrosage, Date_arrosage, Heure_arrosage FROM planification_arrosage WHERE Id_parc = %s"
                value = [id_parc]

                cursor.execute(sql_select, value)

                # Récupération de toutes les lignes de données stocké dans la table "planification_arrosage" de ma base de donnée
                liste_planification_brute = cursor.fetchall()

                liste_planification = []

                for data_raw in liste_planification_brute:
                    id_planning = data_raw[0]
                    id_parc = data_raw[1]
                    duree_arrosage = data_raw[2]
                    date_arrosage = str(data_raw[3])
                    heure_arrosage = str(data_raw[4])
                
                    liste_planification.append([id_planning, id_parc, duree_arrosage, date_arrosage, heure_arrosage])
                return liste_planification, 200
            
            except Exception as e:
                print(f"Erreur survenu lors de la tentative de selection de la liste des prochaines planifications : {e}")
                return [], 400

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return [], 400