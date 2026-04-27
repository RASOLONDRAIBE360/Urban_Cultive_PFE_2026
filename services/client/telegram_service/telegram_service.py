import requests

class TelegramService:

    def select_bot_telegram(self, id_parc, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                # Cette requête indique seulement : une récupération des infos (Token_id, Chat_id_bot) pour l'envoie des notifications telegram de manière ciblé (L'utilisateur ne 
                # reçoit que les notifications en rapport avec les parcelles qu'il a loué uniquement)
                sql_select_bot_telegram = f"SELECT b.Token_bot, b.Chat_id_bot FROM reservation_parc r JOIN bot_telegram b ON r.User_id = b.User_id WHERE r.Id_parc = %s"
                valeur_select_bot_telegram = [id_parc]

                cursor.execute(sql_select_bot_telegram, valeur_select_bot_telegram)
                bot_telegram = cursor.fetchone()

                if bot_telegram:
                    Token_bot = bot_telegram[0]
                    Chat_id_bot = bot_telegram[1]
                    
                    
                    return Token_bot, Chat_id_bot
                
                # Dans le cas où la liste a bien été récupéré mais vide
                print(f"[ALERTE] Info bot telegram récupéré avec succès mais vide pour id_parc = {id_parc}!")
                return None, None
            
            finally:
                db_config.close()
        
        else:
            print(f"[ERREUR] Problème de récupération des info du bot Telegram pour id_parc = {id_parc}")
            return None, None

    def envoyer_notification_telegram(self, id_parc, db_config, msg):

        Token_bot, Chat_id_bot = self.select_bot_telegram(id_parc, db_config)

        # CONFIGURATION PARAMETRIQUE 
        TOKEN = Token_bot
        CHAT_ID = Chat_id_bot

        # URL de l'API pour communiquer avec le bot créé sur Telegram
        url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"

        payload = {
            "chat_id": CHAT_ID,
            "text": msg,
            "parse_mode": "HTML"
        }

        try:
            response = requests.post(url, data=payload)

            if response.status_code == 200:
                print("Notification envoyée avec succès !")

            else:
                print(f"Erreur lors de l'envoi : {response.status_code}")

        except Exception as e:
            print(f"Erreur de connexion : {e}")


