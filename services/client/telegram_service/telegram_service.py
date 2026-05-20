import requests

class TelegramService:

    def select_bot_telegram(self, id_parc, db_config):
        conn, cursor = db_config.connect()

        if conn and cursor:
            try:
                sql_select_user_id_reservation = "SELECT User_id FROM reservation_parc WHERE Id_parc = %s"
                valeur_select_user_id_reservation = [id_parc]

                cursor.execute(sql_select_user_id_reservation, valeur_select_user_id_reservation)
                list_user_id = cursor.fetchone()

                if list_user_id:
                    user_id = list_user_id[0]
                    # Cette requête indique seulement : une récupération des infos (Token_id, Chat_id_bot) pour l'envoie des notifications telegram de manière ciblé (L'utilisateur ne 
                    # reçoit que les notifications en rapport avec les parcelles qu'il a loué uniquement)
                    sql_select_bot_telegram = f"SELECT Token_bot, Chat_id_bot FROM bot_telegram WHERE User_id = %s"
                    valeur_select_bot_telegram = [user_id]

                    cursor.execute(sql_select_bot_telegram, valeur_select_bot_telegram)
                    bot_telegram = cursor.fetchone()

                    if bot_telegram:
                        Token_bot = bot_telegram[0]
                        Chat_id_bot = bot_telegram[1]
                    
                        return Token_bot, Chat_id_bot
                
                    else:
                        print(f"[ALERTE] Aucune configuration Telegram trouvée pour l'utilisateur {user_id}")
                        return None, None

                else:
                    print(f"[ALERTE] Aucune réservation trouvée pour id_parc = {id_parc}")
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


