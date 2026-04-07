import requests
import json
import time

BASE_URL = "http://localhost:5000"

def test_led_on(id_parc):
    print(f"\n--- TEST : Allumage de la pompe {id_parc} ---")
    payload = {"duree_arrosage_manuelle": 10}
    try:
        response = requests.post(f"{BASE_URL}/led/on/id_parc={id_parc}", json=payload)
        print(f"Status Code: {response.status_code}")
        print(f"Response: {response.json()}")
        return response.status_code == 200
    except Exception as e:
        print(f"Erreur : {e}")
        return False

def test_led_off(id_parc):
    print(f"\n--- TEST : Extinction de la pompe {id_parc} ---")
    payload = {"list_id_parc": [id_parc]}
    try:
        response = requests.post(f"{BASE_URL}/led/off", json=payload)
        print(f"Status Code: {response.status_code}")
        print(f"Response: {response.json()}")
        return response.status_code == 200
    except Exception as e:
        print(f"Erreur : {e}")
        return False

def check_status(id_parc):
    print(f"\n--- CHECK : Statut de la pompe {id_parc} ---")
    try:
        response = requests.get(f"{BASE_URL}/status/pompe/id_parc={id_parc}")
        print(f"Statut actuel : {response.json()['etat_pompe_dict']}")
    except Exception as e:
        print(f"Erreur : {e}")

if __name__ == "__main__":
    parcelle = "OP_001"
    
    # 1. Vérifier statut initial
    check_status(parcelle)
    
    # 2. Tenter d'allumer
    if test_led_on(parcelle):
        print("Commande ON envoyée avec succès.")
    
    # 3. Attendre un peu (simuler temps de réponse hardware/MQTT)
    print("Attente de 1 secondes...")
    time.sleep(1)
    
    # 4. Vérifier si le statut a changé
    check_status(parcelle)
    
    # 5. Tenter d'éteindre
    if test_led_off(parcelle):
        print("Commande OFF envoyée avec succès.")
    
    # 6. Attendre
    print("Attente de 1 secondes...")
    time.sleep(1)
    
    # 7. Vérifier statut final
    check_status(parcelle)
