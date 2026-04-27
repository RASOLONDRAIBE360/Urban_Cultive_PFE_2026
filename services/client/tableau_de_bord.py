

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

    



