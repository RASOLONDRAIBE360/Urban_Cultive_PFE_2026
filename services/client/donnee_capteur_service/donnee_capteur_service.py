from email import message
from models.CRUD.client.dashboard_iot.donnee_capteur.donnee_capteur import DonneeCapteurModel

class DonneeCapteurService:

    def create_donnee_capteur(self, choix_parcelle, hum, temp, lum, type_a, msg, db_config):
        nouvelle_donnee_capteur = DonneeCapteurModel(
            id_parc = choix_parcelle,
            humidite = hum,
            temperature = temp,
            luminosite = lum,
            type_alerte = type_a,
            message = msg
        )

        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                sql_create = f"INSERT INTO {nouvelle_donnee_capteur.__tablename__} (Id_parc, Humidite, Temperature, Luminosite, Type_alerte, Message) VALUES (%s, %s, %s, %s, %s, %s)"
                valeurs = [nouvelle_donnee_capteur.Id_parc, nouvelle_donnee_capteur.Humidite, nouvelle_donnee_capteur.Temperature, nouvelle_donnee_capteur.Luminosite, nouvelle_donnee_capteur.Type_alerte, nouvelle_donnee_capteur.Message]

                cursor.execute(sql_create, valeurs)
                conn.commit()
                return 200
            
            finally:
                db_config.close()
        
        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 400
            
    def select_donnee_capteur(self, choix_parcelle, db_config):

        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                sql_select = f"SELECT id_parc, humidite, temperature, luminosite, type_alerte, message, date_pub FROM donnee_capteur WHERE Id_parc=%s"
                valeur = [choix_parcelle]

                cursor.execute(sql_select, valeur)
                liste_donnee_capteur_brute = cursor.fetchall()

                liste_donnee_capteur = []

                for data_raw in liste_donnee_capteur_brute:
                    id_parc = data_raw[0]
                    humidite = data_raw[1]
                    temperature = data_raw[2]
                    luminosite = data_raw[3]
                    type_alerte = data_raw[4]
                    message = data_raw[5]
                    date_pub = str(data_raw[6])

                    liste_donnee_capteur.append([id_parc, humidite, temperature, luminosite, type_alerte, message, date_pub])
                return liste_donnee_capteur, 200

            finally:
                db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return [], 400
