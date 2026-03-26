import joblib
import pandas as pd 

class AgriAIService:
    # Cette fonction est le constructeur de la classe AgriAIService.
    # Elle est appelée automatiquement lors de la création d'un objet de la classe AgriAIService.
    def __init__(self):
        # chargement du modèle sauvegardé dans le fichier ".pkl" validé

        # Etant donné que j'ai utilisé l'argument "-m" dans la commande pour le lancement de mon serveur
        # flask -> ce qui veut dire que python va lancer le script dans "controllers.client.tableau_de_bord"
        # depuis la racine de mon projet -> "Document_PFE". C'est pourquoi nous partons donc de "models".
        self.model_agri_ai = joblib.load("./models/model_agri_ai/save_data_agri_ai/model_agri_ai_v0.2.pkl")

    # Cette fonction est le coeur du système.
    # Elle prend en paramètre l'humidité, la température et la luminosité.
    # Elle retourne la recommandation "Métier" sous forme de dictionnaire.
    # C'est elle qui assure la prédiction du label par le modèle. Et sera appelée par l'API du serveur Flask.
    def predict_label(self, hum, temp, lum):
        # Création du DataFrame avec les données d'entrée
        input_data_to_predict = pd.DataFrame([{
            "humidity": hum,
            "temperature": temp,
            "luminosity": lum
        }])

        # Prédiction du label.
        # Etant donné que la valeur de label qui sera prédit par le modèle sera renvoyé
        # sous forme de tableau (array) par la bibliotheque numpy, nous aurons comme
        # valeur ["optimal"] ou ["sec"] ou ["tres_sec"] ou ["humide"] ou ["tres_humide"]
        # ou ["inconnu"] pour en extraire la valeur nous allons donc utiliser l'indexation
        # [0] pour obtenir la valeur seule du tableau.
        predicted_label = self.model_agri_ai.predict(input_data_to_predict)[0]
        
        # Traduction en recommandation "Métier"
        instruction_metier = {
            "optimal": {
                "type_alerte": "optimal",
                "msg": "Le sol est optimal, pas d'arrosage nécessaire.",
                "action": "Aucune action requise.",
                "humidity": hum,
                "temperature": temp,
                "luminosity": lum
            },

            "tres_sec": {
                "type_alerte": "tres_sec",
                "msg": "Le sol est trop sec, il faut arroser.",
                "action": "Arroser le sol abondamment.",
                "humidity": hum,
                "temperature": temp,
                "luminosity": lum
            },

            "sec": {
                "type_alerte": "sec",
                "msg": "Le sol est sec, il faut arroser.",
                "action": "Arroser le sol.",
                "humidity": hum,
                "temperature": temp,
                "luminosity": lum
            },

            "humide": {
                "type_alerte": "humide",
                "msg": "Le sol est humide, pas d'arrosage nécessaire.",
                "action": "Aucune action requise.",
                "humidity": hum,
                "temperature": temp,
                "luminosity": lum
            },

            "tres_humide": {
                "type_alerte": "tres_humide",
                "msg": "Le sol est trop humide, il faut éviter d'arroser.",
                "action": "Ne pas arroser le sol.",
                "humidity": hum,
                "temperature": temp,
                "luminosity": lum
            },

            "inconnu": {
                "type_alerte": "inconnu",
                "msg": "Le sol est dans un état inconnu, il faut vérifier.",
                "action": "Vérifier l'état du sol.",
                "humidity": hum,
                "temperature": temp,
                "luminosity": lum
            }
        }
        # Retourner la recommandation
        return instruction_metier[predicted_label]