import pandas as pd
import numpy as np
import os

def generate_agri_data(taille_dataset=1000):
    # Pour fixer une graine aléatoire
    # pour obtenir les mêmes résultats à chaque 
    # regénération aléatoire de donnée par la fonction np.random
    np.random.seed(42)

    # Génération de données de base avec des plages réalistes avec une distribution
    # uniforme (distribution uniforme -> ce qui signifie que tous les nombres dans 
    # l'intervalle donné auront la même probabilité d'être choisis)
    humidity = np.random.uniform(5, 95, taille_dataset).astype(int) # 5% - 95%
    temperature = np.random.uniform(12, 42, taille_dataset) # 12°C - 42°C
    luminosity = np.random.uniform(0, 1000, taille_dataset) # 0 - 1000 lux

    label = []
    
    for i in range(taille_dataset):
        hum, temp, lum = humidity[i], temperature[i], luminosity[i]
        
        # LOGIQUE METIER (Règles pour attribuer les labels aux données)
        if hum < 40 and temp > 35 and lum < 500:
            label.append("tres_sec")
        elif hum < 60 and temp > 30 and lum < 700:
            label.append("sec")
        elif hum < 70 and temp > 25 and lum < 800:
            label.append("optimal")
        elif hum < 80 and temp > 20 and lum < 900:
            label.append("humide")
        elif hum >= 80 and temp <= 20 and lum >= 900:
            label.append("tres_humide")
        else:
            label.append("inconnu")

    # Définition de la structure de donnée pour la création du DataFrame
    data_df = pd.DataFrame({
        "humidity": humidity,
        "temperature": temperature.round(2), # round(2) -> pour arrondir les nombres à 2 décimales
        "luminosity": luminosity.round(2),
        "label": label
    })

    # Sauvegarde du DataFrame dans un fichier CSV sans rajouter la colonne 
    # spécifions le numéro des lignes (les index) dans le fichier CSV (c'est pourquoi index = False)
    data_df.to_csv("./dataset/agri_dataset.csv", index=False)

    print(f"Dataset généré avec succès avec {taille_dataset} lignes.")
    print("\nDistribution des classes :")
    print(data_df["label"].value_counts())

    return data_df

if __name__ == "__main__":
    generate_agri_data(taille_dataset=1000)