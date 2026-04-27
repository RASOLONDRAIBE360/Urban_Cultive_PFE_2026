class SeuillageService():

    def __init__(self, db_config):
        self.db_config = db_config

    def updateSeuilData(self, seuilCapteurModel):
        conn, cursor = self.db_config.connect()

        if conn and cursor:
            try:
                sql_verif_data = f"SELECT * FROM {seuilCapteurModel.__tablename__} WHERE Id_parc = %s"
                valeur_verif_data = [seuilCapteurModel.Id_parc]

                cursor.execute(sql_verif_data, valeur_verif_data)
                existe_deja = cursor.fetchone()

                if existe_deja:
                    sql_update = f"UPDATE seuillage SET Temp_seuil = %s, Humidite_seuil = %s WHERE Id_parc = %s" 
                    valeurs_update = [seuilCapteurModel.Temp_seuil, seuilCapteurModel.Humidite_seuil, seuilCapteurModel.Id_parc]

                    cursor.execute(sql_update, valeurs_update)
                    conn.commit()
                    return 200
                
                else:
                    return 400
            
            finally:
                self.db_config.close()
        
        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 400

    def selectSeuilData(self, id_parc):
        conn, cursor = self.db_config.connect()
        
        if conn and cursor:
            try:
                sql_select = f"SELECT * FROM seuillage WHERE Id_parc = %s"
                valeur_select = [id_parc]

                cursor.execute(sql_select, valeur_select)
                liste_seuil_data = cursor.fetchall()

                if liste_seuil_data:
                    return liste_seuil_data, 200
                
                else:
                    return [], 400
            
            finally:
                self.db_config.close()
        
        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return [], 400

    def createSeuilData(self, seuilCapteurModel):
        conn, cursor = self.db_config.connect()

        if conn and cursor :
            try:
                sql_create = f"INSERT INTO {seuilCapteurModel.__tablename__} (Id_parc, Temp_seuil, Humidite_seuil) VALUES (%s, %s, %s)"
                valeurs_create = [seuilCapteurModel.Id_parc, seuilCapteurModel.Temp_seuil, seuilCapteurModel.Humidite_seuil]

                cursor.execute(sql_create, valeurs_create)
                conn.commit()
                return 200
            
            finally:
                self.db_config.close()

        else:
            print("Erreur survenu lors de la tentative de connexion à la base de donnée")
            return 400

