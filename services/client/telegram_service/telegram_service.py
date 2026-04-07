import requests

class TelegramService:
        
    def envoyer_notification_telegram(self, msg):
        # CONFIGURATION PARAMETRIQUE 
        TOKEN = "8703197589:AAG1ODLJF2fYERFfQoXxCdM6GcdnM_gC5Og"
        CHAT_ID = "8746700326"

        # URL de l'API pour communiquer avec le bot créé sur Telegram
        url = f"https://api.telegram.org/bot{TOKEN}/sendMessage"

        payload = {
            "chat_id": CHAT_ID,
            "text": msg,
            "parse_mode": "Markdown"
        }

        try:
            response = requests.post(url, data=payload)

            if response.status_code == 200:
                print("Notification envoyée avec succès !")

            else:
                print(f"Erreur lors de l'envoi : {response.status_code}")

        except Exception as e:
            print(f"Erreur de connexion : {e}")


