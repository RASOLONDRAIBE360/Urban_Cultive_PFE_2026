class CreateAlerteAI:
    # Ici je fais une injection de dépendance pour permettre l'utilisation du script db_config.py
    # dans la fonction create_alerte_ai. C'est pour éviter les problèmes de chemin d'accès au script db_config.py
    # (qui se trouve dans un dossier différent de celui de la fonction create_alerte_ai)

    # Grâce à ce constructeur "__init__" ma classe d'instanciation attendra juste que l'on lui passe en argument la valeur
    # de db_config (lors de la création d'un objet) sans plus avoir à créer sa propre connexion à la base de données
    def __init__(self, db_config):

        # Utilisation du script db_config.py pour se connecter à la base de données
        self.db_config = db_config

    def create_alerte_ai(self, id_parc, diagnostic):
        try:
            # Connexion à la base de données
            conn, cursor = self.db_config.connect()
            
            # Condition pour s'assurer que la connexion à la base de données est bien établie
            if self.db_config.conn.is_connected():
                print("Connexion à la base de données réussie")

                # Requête pour l'insertion de l'alerte dans la base de données
                query = """
                INSERT INTO alertes_ai (id_parc, type_alerte, contenu_msg)
                VALUES (%s, %s, %s)
                """

                # Définition de la valeur à la place des %s
                values = (
                    id_parc,
                    diagnostic["type_alerte"],
                    diagnostic["msg"]
                )
                
                # Envoie et exécution de la requête SQL dans la base de donnée
                cursor.execute(query, values)

                # Validation de la transaction
                conn.commit()

                print(f"Alerte IA traitée et insérée avec succès dans la base de donnée :\n{diagnostic}")

        except Exception as e:
            print("Erreur survenu lors de la tentative d'insertion dans la base de donnée.")
        
        # Fermeture de la connexion à la base de données (toujours exécutée, qu'il y ait eu une erreur ou non)
        finally:
            self.db_config.close()
