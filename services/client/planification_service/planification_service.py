from datetime import datetime, timedelta

from models.CRUD.client.dashboard_iot.planification_arrosage.planification_arrosage import PlanificationModel

from services.client.led_service.led_service import LedService
from services.client.donnee_capteur_service.recuperate_data_sensor_mqtt import RecuperateDataSensorMQTT
from services.client.telegram_service.telegram_service import TelegramService

class PlanificationService:
    ledService = LedService()
    telegramService = TelegramService()
    mqtt_service = RecuperateDataSensorMQTT()

    def fonction_led_programmer(self, id_parc, duree_arrosage, id_planning, db_config, scheduler):
        self.ledService.led_on_thread(id_parc, self.mqtt_service.mqtt_client, duree_arrosage)

        print(f"[SYSTEM] Planification démarrée pour {id_parc} (Durée: {duree_arrosage}s)")

        # On vérifie s'il reste des exécutions futures
        job = scheduler.get_job(id_planning)

        # Si le job n'existe plus (tâche unique finie) ou n'a plus de date prévue (fin d'intervalle)
        if not job or job.next_run_time is None:
            print(f"[SYSTEM] Fin de planification pour {id_planning}. Désactivation du toggle.")

            # Le bouton est de départ sur le status "Actif". Ce qu'on cherche de faire c'est une mise à jour pour le mettre sur "Inactif"
            self.update_status_planification_arrosage(scheduler, id_parc, id_planning, False, db_config)

    def planifier(self, id_parc, scheduler, date_str, duree, intervalle_repetition, nombre_repetition, db_config):

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
                print(f"[ALERTE] Un arrosage avec id_planning = {verif_job_id} est déjà planifié à la même heure")
                return verif_job_id, 409
            
            else:
                # Configuration du déclencheur (Trigger)
                # Par défaut lorsque la répétition de tâche n'a pas été activé par l'utilisateur
                trigger_type = 'date' # Nous spécifions ici le type de déclencheur en "date" pour dire que la tâche sera exécutée à une date et une heure précise
                trigger_args = {
                    'run_date': date_heure # Nous spécifions ici la date et l'heure exacte à laquelle la tâche sera exécutée
                }
                
                # L'option "répétition tâche" a été activé par l'utilisateur
                if nombre_repetition > 1:
                    trigger_type = 'interval'
                    # Calcul de la fin : date de début + (intervalle * (nombre de répétitions - 1))
                    # On ajoute 1 seconde de marge pour être sûr que la dernière exécution passe
                    
                    # Ex: 5s (arrosage) + 5s (pause) = 10s entre chaque début
                    intervalle_reel = int(duree) + int(intervalle_repetition)

                    date_fin = date_heure + timedelta(seconds=(int(intervalle_reel) * (int(nombre_repetition) - 1)) + 1)
                    trigger_args = {
                        'seconds': int(intervalle_reel),
                        'start_date': date_heure,
                        'end_date': date_fin
                    }

                """
                    Ici nous utilisons APScheduler pour ajouter une tâche planifiée.
                """
                # Déclenchement de l'allumage défini selon la valeur -> date_heure ayant été planifié par l'utilisateur depuis l'interface streamlit
                job_start = scheduler.add_job(
                    id = f"start_{id_parc}_{date_heure}",
                    func = lambda: self.fonction_led_programmer(id_parc, duree, verif_job_id, db_config, scheduler), #Avec "lambda" nous retardons l'exécution de notre fonction en fonction du délais de planification dont nous avons indiqué ci-dessus à l'APScheduler. Afin d'éviter une exécution immédiate de la fonction 
                    trigger = trigger_type, 
                    **trigger_args, # On injecte les arguments dynamiquement
                )

                # ---------------- INSERTION DANS LA BASE DE DONNEE -----------------
                # Dans le cas où la planification a bien réussi alors la fonction "add_job" nous renvoie un objet "Job". A partir de cette objet nous allons en extraire son Id pour faire une vérification que la planification a bien eu lieu 
                if job_start.id:
                    print(f"Succès matériel : Job {job_start.id} planifié.")

                    date_arrosage = date_heure.strftime("%Y-%m-%d")
                    heure_arrosage = date_heure.strftime("%H:%M")

                    status_code = self.create_planification_arrosage(
                        job_start.id, 
                        id_parc, duree, 
                        date_arrosage, 
                        heure_arrosage, 
                        intervalle_repetition, 
                        nombre_repetition, 
                        db_config
                    )

                    if status_code == 200:
                        print(f"Insertion de {job_start.id} réussi dans la base de donnée pour id_parc={id_parc}")
                        return job_start.id, 200
                                
                    else:
                        # Dans le cas où l'insertion à la base de donnée échoue alors nous allons annuler le job pour rester cohérent
                        scheduler.remove_job(job_start.id)
                        print(f"Erreur survenue lors de la tentative d'insertion de {job_start.id} dans la base de donnée pour id_parc={id_parc}")
                        return None, 400
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

    def create_planification_arrosage(self, id_plann, id_parc, duree_arr, date_arr, heure_arr, intervalle_repetition, nombre_repetition, db_config):
        # Instanciation de la classe "Model" + initialisation des champs de ma table "planification_arrosage" stocké en base de donnée
        nouvelle_planification_arrosage = PlanificationModel(
            id_plann,
            id_parc,
            duree_arr,
            date_arr,
            heure_arr,
            intervalle_repetition,
            nombre_repetition,
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
                    sql_create = f"INSERT INTO {nouvelle_planification_arrosage.__tablename__} (Id_planning, Id_parc, Duree_arrosage, Date_arrosage, Heure_arrosage, Intervalle_rep, Nombre_freq) VALUES (%s, %s, %s, %s, %s, %s, %s)"

                    # Après que nous ayons initialisés la valeur des champs pour la table "planification_arrosage" via le constructeur. Nous allons les récupérés à partir de l'instance de la classe nommée
                    # nouvelle_planification_arrosage
                    valeurs = [
                        nouvelle_planification_arrosage.Id_planning, 
                        nouvelle_planification_arrosage.Id_parc, 
                        nouvelle_planification_arrosage.Duree_arrosage, 
                        nouvelle_planification_arrosage.Date_arrosage, 
                        nouvelle_planification_arrosage.Heure_arrosage, 
                        nouvelle_planification_arrosage.Intervalle_rep,
                        nouvelle_planification_arrosage.Nombre_freq,
                    ]

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

    def select_liste_planification_arrosage(self, id_parc, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:

                # Je cite les noms des colonnes ici pour respecter le même ordre lors de la récupération et affichage des données sur mon interface streamlit
                sql_select = "SELECT Id_planning, Id_parc, Duree_arrosage, Date_arrosage, Heure_arrosage, Intervalle_rep, Nombre_freq, Status FROM planification_arrosage WHERE Id_parc = %s"
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
                    intervalle_rep = data_raw[5]
                    nombre_freq = data_raw[6]
                    status = data_raw[7]
                
                    liste_planification.append([id_planning, id_parc, duree_arrosage, date_arrosage, heure_arrosage, intervalle_rep, nombre_freq, status])
                return liste_planification, 200
            
            except Exception as e:
                print(f"Erreur survenu lors de la tentative de selection de la liste des prochaines planifications : {e}")
                return [], 400

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return [], 400

    def select_planification_arrosage(self, id_parc, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:

                # Je cite les noms des colonnes ici pour respecter le même ordre lors de la récupération et affichage des données sur mon interface streamlit
                sql_select = "SELECT Id_planning FROM planification_arrosage WHERE Id_parc = %s"
                value = [id_parc]

                cursor.execute(sql_select, value)

                # Récupération de toutes les lignes de données stocké dans la table "planification_arrosage" de ma base de donnée
                liste_planification_brute = cursor.fetchall()

                liste_planification = []

                for data_raw in liste_planification_brute:
                    id_planning = data_raw[0]
                
                    liste_planification.append([id_planning])

                return liste_planification, 200
            
            except Exception as e:
                print(f"Erreur survenu lors de la tentative de selection de la liste des prochaines planifications : {e}")
                return [], 400

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return [], 400

    #def update_planifier(self, scheduler, id_parc, id_planning, nouveau_moment_programme, nouveau_duree_arrosage, nouveau_intervalle_rep, nouveau_nombre_freq, db_config):
    #    if scheduler.get_job(id_planning):
    #        print(f"[INFO] Une planification d'arrosage avec id_planning = {id_planning} détecté avec succes !")
            
    #        # On convertit la chaîne ayant été reçue par requête json (au niveau du controller -> "YYYY-MM-DD HH:MM") en objet datetime
    #        nouveau_moment_programme = datetime.strptime(nouveau_moment_programme, "%Y-%m-%d %H:%M")
            
    #        # On extrait la date et l'heure pour l'arrosage séparément
    #        nouveau_date_arrosage = nouveau_moment_programme.strftime("%Y-%m-%d")
    #        nouveau_heure_arrosage = nouveau_moment_programme.strftime("%H:%M")

    #        status_code = self.update_info_planification(id_planning, nouveau_date_arrosage, nouveau_heure_arrosage, nouveau_duree_arrosage, nouveau_intervalle_rep, nouveau_nombre_freq, db_config)

    #        if status_code == 200:
    #            # Modification de la durée (Les arguments de la fonction)
    #            scheduler.modify_job(id_planning, args=[id_parc, nouveau_duree_arrosage])

    #            trigger_type = 'date'
    #            trigger_args = {
    #                'run_date': nouveau_moment_programme
    #            }

    #            if int(nouveau_intervalle_rep) > 1:
    #                trigger_type = 'interval'
    #                date_fin = nouveau_moment_programme + timedelta(seconds=(int(nouveau_intervalle_rep) * (int(nouveau_nombre_freq) - 1)) + 1)
    #                trigger_args = {
    #                    'seconds': int(nouveau_intervalle_rep),
    #                    'start_date': nouveau_moment_programme,
    #                    'end_date': date_fin
    #                }
                
    #            scheduler.reschedule_job(
    #                id_planning, 
    #                trigger=trigger_type,
    #                **trigger_args
    #                )
                
    #            return 200
    #        return 400

    def gestion_job_planification_arrosage(self, scheduler, id_parc, mode_systeme_update, db_config):
        # 1. On définit le nouveau statut selon le mode
        nouveau_status = "Inactif" if mode_systeme_update == "Auto" else "Actif"
        
        # 2. Prioriser une mise à jour du status des planifications arrosage stocké en base de donnée (même si le scheduler est vide après redémarrage)
        self.update_status_planification_arrosage_mode_function(id_parc, nouveau_status, db_config)

        list_data_planification, status_code = self.select_planification_arrosage(id_parc, db_config)

        # On envoie une notification telegram uniquement s'il y a des planifications stocké en base de donnée
        if list_data_planification:
            if mode_systeme_update == "Auto":
                msg_final_send = f"<b>⏸️ SUSPENSION</b> de toutes les planifications pour la parcelle <b>{id_parc}</b> (Mode AUTO activé)."
                self.telegramService.envoyer_notification_telegram(id_parc, db_config, msg_final_send)
            
            elif mode_systeme_update == "Manuel":
                msg_final_send = f"<b>▶️ REPRISE</b> des planifications pour la parcelle <b>{id_parc}</b> (Mode MANUEL activé)."
                self.telegramService.envoyer_notification_telegram(id_parc, db_config, msg_final_send)

        for p in list_data_planification:
            # On récupère l'ID qui est la première (et seule) valeur du petit tableau p
            id_planning = p[0]

            job = scheduler.get_job(str(id_planning))

            if job:
                if mode_systeme_update == "Auto":
                    # Si on passe en auto, on "endort" toutes les planifications pour qu'il ne se déclenche pas
                    job.pause()
                    print(f"[MODE AUTO] Job {id_planning} suspendu.")

                elif mode_systeme_update == "Manuel":
                    # On relance le job
                    job.resume()
                    print(f"[MODE MANUEL] Job {id_planning} réactivé.")

            if mode_systeme_update == "Manuel" and not job:
                msg_final_send = (
                    f"<b>⚠️ PLANIFICATION MANQUÉE</b> pour la parcelle <b>{id_parc}</b> (ID: {id_planning}). Elle a expiré pendant le mode AUTO."
                )

                self.telegramService.envoyer_notification_telegram(id_parc, db_config, msg_final_send)

                print(f"[ERREUR -> MODE MANUEL] Job {id_planning} expiré.")                    

    def update_status_planification_arrosage(self, scheduler, id_parc, id_planning, status, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                # 1. Extraction en base des configurations passées de l'utilisateur. Pour vérification de l'existance de la configuration en base de donnée avant d'effectuer l'action de mise à jour
                sql_select = "SELECT * FROM planification_arrosage WHERE Id_planning = %s and Id_parc = %s"
                valeur = [id_planning, id_parc]
                
                cursor.execute(sql_select, valeur)
                info_planification_arrosage = cursor.fetchone()

                if info_planification_arrosage:
                    # Le bouton toggle a une valeur "Actif" au début comme elle activé. C'est pourquoi nous
                    # inversons la logique pour forcer la valeur à "Inactif" pour une mise à jour du bouton
                    status_update = "Inactif" if status == False else "Actif"

                    job = scheduler.get_job(str(id_planning))

                    if job:
                        if status_update == "Inactif":
                            job.pause()
                        
                        else:
                            job.resume()

                    sql_update= "UPDATE planification_arrosage SET Status = %s WHERE Id_planning = %s and Id_parc = %s"

                    valeurs = [status_update, id_planning, id_parc]

                    cursor.execute(sql_update, valeurs)
                    conn.commit()
                    return 200
                
                else:
                    print(f"Erreur de la mise à jour du status de planification -> Id_parc = {id_parc} introuvable")
                    return 400

            finally:
                db_config.close()

        else:
            print("Vous êtes ici")
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 500 

    def update_status_planification_arrosage_mode_function(self, id_parc, nouveau_status_planification, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                # 1. Extraction en base des configurations passées de l'utilisateur. Pour vérification de l'existance de la configuration en base de donnée avant d'effectuer l'action de mise à jour
                sql_select = "SELECT * FROM planification_arrosage WHERE Id_parc = %s"
                valeur = [id_parc]
                
                cursor.execute(sql_select, valeur)
                info_planification_arrosage = cursor.fetchall()

                if info_planification_arrosage:
                    maintenant = datetime.now()

                    for row in info_planification_arrosage:
                        id_planning = row[0]
                        date_arrosage = row[3] # Objet date
                        heure_arrosage = row[4] # Objet time ou timedelta

                        date_obj = datetime.strptime(date_arrosage, "%Y-%m-%d").date() if isinstance(date_arrosage, str) else date_arrosage

                        # --- PROTECTION TIMEDELTA ---
                        # Si MySQL renvoie un timedelta, on le transforme en objet time
                        if isinstance(heure_arrosage, timedelta):
                            heure_obj = (datetime.min + heure_arrosage).time()
                        
                        elif isinstance(heure_arrosage, str):
                            # On s'assure d'avoir un format HH:MM même si l'heure est à un seul chiffre (ex: 8:30:00 -> 08:30)
                            parts = heure_arrosage.split(':')
                            heure_formatee = f"{int(parts[0]):02d}:{int(parts[1]):02d}"
                            heure_obj = datetime.strptime(heure_formatee, "%H:%M").time()
                        else:
                            heure_obj = heure_arrosage
                        
                        moment_programme = datetime.combine(date_obj, heure_obj)

                        # Par défaut, on prend le statut demandé (Actif ou Inactif)
                        status_a_enregistrer = nouveau_status_planification

                        # LOGIQUE : Si on veut activer mais que c'est expiré, on force "Inactif"
                        if nouveau_status_planification == "Actif":
                            if moment_programme < maintenant:
                                status_a_enregistrer = "Inactif" 
                                                    
                        # Si on n'est pas passé par le "continue", alors on met à jour
                        sql_update= "UPDATE planification_arrosage SET Status = %s WHERE Id_planning = %s"

                        # nouvel_status_planification = "Actif" ou "Inactif" en fonction de l'action effectuer par l'utilisateur sur le bouton toggle
                        valeurs = [status_a_enregistrer, id_planning]

                        cursor.execute(sql_update, valeurs)
                        conn.commit()
                    return 200
                    
                else:
                    print(f"Erreur de la mise à jour du status de planification -> Id_parc = {id_parc} introuvable")
                    return 400

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 400            

    #def update_info_planification(self, id_planning, nouveau_date_arrosage, nouveau_heure_arrosage, nouveau_duree_arrosage, nouveau_intervalle_rep, nouveau_nombre_freq, db_config):
    #    conn, cursor = db_config.connect()

    #    if conn and cursor:
    #        try:
    #            # 1. Extraction en base des configurations passées de l'utilisateur. Pour vérification de l'existance de la configuration en base de donnée avant d'effectuer l'action de mise à jour
    #            sql_select = "SELECT * FROM planification_arrosage WHERE Id_planning = %s"
    #            valeur = [id_planning]
                
    #            cursor.execute(sql_select, valeur)
    #            info_planification_arrosage = cursor.fetchall()

    #            if info_planification_arrosage:
    #                sql_update= "UPDATE planification_arrosage SET Duree_arrosage = %s, Date_arrosage = %s, Heure_arrosage = %s, Intervalle_rep = %s, Nombre_freq = %s WHERE Id_planning = %s"

    #                # nouvel_status_planification_repetition = "Actif" ou "Inactif" en fonction de l'action effectuer par l'utilisateur sur le bouton toggle
    #                valeurs = [nouveau_duree_arrosage, nouveau_date_arrosage, nouveau_heure_arrosage, nouveau_intervalle_rep, nouveau_nombre_freq, id_planning]

    #                cursor.execute(sql_update, valeurs)
    #                conn.commit()
    #                return 200
                
    #            else:
    #                print(f"Erreur de la mise à jour du status de planification -> Id_planning = {id_planning} introuvable")
    #                return 400

    #        finally:
    #            db_config.close()

    #    else:
    #        print("Erreur survenu lors de la tentative de connexion à la base de donnée")
    #        return 400 
    
    def select_mode(self, id_parc, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:

                # Je cite les noms des colonnes ici pour respecter le même ordre lors de la récupération et affichage des données sur mon interface streamlit
                sql_select = "SELECT Operation_mode FROM mode_systeme WHERE Id_parc = %s"
                value = [id_parc]

                cursor.execute(sql_select, value)

                mode_systeme_actuel = cursor.fetchone()

                if mode_systeme_actuel:
                    return mode_systeme_actuel[0], 200
                
                else:
                    return None, 400
            
            except Exception as e:
                print(f"Echec du tentative pour la récupération du mode systeme : {e}")
                return None, 400

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return None, 400

    def update_mode(self, id_parc, mode_systeme_actuel, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                # 1. Extraction en base des configurations passées de l'utilisateur. Pour vérification de l'existance de la configuration en base de donnée avant d'effectuer l'action de mise à jour
                sql_select = "SELECT * FROM mode_systeme WHERE Id_parc = %s"
                valeur = [id_parc]
                
                cursor.execute(sql_select, valeur)
                info_mode_systeme = cursor.fetchone()

                if info_mode_systeme:
                    mode_systeme_update = "Manuel" if mode_systeme_actuel == "Auto" else "Auto"

                    sql_update= "UPDATE mode_systeme SET Operation_mode = %s WHERE Id_parc = %s"

                    # nouvel_status_planification_repetition = "Actif" ou "Inactif" en fonction de l'action effectuer par l'utilisateur sur le bouton toggle
                    valeurs = [mode_systeme_update, id_parc]

                    cursor.execute(sql_update, valeurs)
                    conn.commit()
                    return mode_systeme_update, 200
                
                else:
                    print(f"Erreur de la mise à jour du mode du système -> Id_parc = {id_parc} introuvable")
                    return None, 400

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return None, 400 