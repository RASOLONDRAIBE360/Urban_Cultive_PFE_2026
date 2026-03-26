import mysql.connector
from mysql.connector import Error

class DBConfig:

    # Paramètres de connexion à la base de données
    DB_SETTINGS = {
        "host": "localhost",
        "port": 3306,
        "user": "root",
        "password": "",
        "database": "gestion_participatif"
    }

    def connect(self):
        try:
            # ** (suivi de self.DB_SETTINGS -> permet de récupérer les paramètres de connexion à la base de
            # données pour l'insérer étant des arguments à la fonction .connect())
            # RESULTAT : mysql.connector.connect(host="localhost", user="root", password="", database="gestion_partigestion_participatif")
            # Chaque paire (clé, valeur) ont été récupérés du dictionnaire self.DB_SETTINGS et ont été insérés
            # comme arguments à la fonction .connect()
            self.conn = mysql.connector.connect(**self.DB_SETTINGS)

            # self.cursor -> permet de créer un curseur pour exécution des requêtes SQL
            self.cursor = self.conn.cursor()

            if self.conn.is_connected():
                print("Connexion à la base de données réussie")
                return self.conn, self.cursor

        except Error as e:
            print(f"Erreur de connexion à la base de données : {e}")
            return None, None
    
    def close(self):
        # hasattr(self, "conn") -> permet de vérifier si l'attribut "conn" existe bien dans l'objet "self"
        if hasattr(self, "conn") and self.conn is not None and self.conn.is_connected():
            self.cursor.close()
            self.conn.close()
            print("Connexion à la base de données fermée avec succès.")

