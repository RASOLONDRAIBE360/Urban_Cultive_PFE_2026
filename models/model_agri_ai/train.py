import pandas as pd
import matplotlib.pyplot as plt
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, ConfusionMatrixDisplay, classification_report
import joblib


# 1 - Charger le dataset expert
agri_dataset = pd.read_csv("./dataset/agri_dataset_v0.2.csv")

# 2 - Prétraitement des données (pour séparer la colonne des données
# cible avec les données features/caractéristiques)
X = agri_dataset.drop("label", axis=1)
Y = agri_dataset["label"]

# 3 - Diviser le dataset en ensembles d'entraînement et de test 
# (80% des données pour l'entraînement et 20% pour le test)
# random_state=42 -> pour fixer une graine aléatoire
X_train, X_test, Y_train, Y_test = train_test_split(X, Y, test_size=0.2, random_state=42)

# 4 - Créer et entraîner le modèle
# n_estimators -> nombre d'arbres dans le forêt aléatoire
# Dans notre cas nous avons pris 100 arbres (100 qui est la valeur par défaut
# dans la fonction RandomForestClassifier() de sklearn). Grâce à ces 100 arbres
# nous pouvons évaluer de nombreux cas différents possible et ainsi obtenir une 
# meilleure précision dans nos prédictions.
model_agri_ai = RandomForestClassifier(n_estimators=100, random_state=42)
model_agri_ai.fit(X_train, Y_train)

# 5 - Evaluation du modèle
# TEST D'ACCURACY (pour évaluer à quelle point le modèle est capable de prédire
# correctement les labels sur des données qu'il n'a jamais vu auparavant))
Y_pred = model_agri_ai.predict(X_test)
accuracy = accuracy_score(Y_test, Y_pred)

# accuracy * 100.0 -> pour convertir le résultat en pourcentage
# .2f -> pour arrondir le résultat à 2 décimales
print(f"Accuracy du modèle : {accuracy * 100.0:.2f}%")

# Création affichage matrice de confusion afin de visualiser les détails nécessaire
# pour les prochaines test d'évaluation de la fiabilité et la qualité du modèle d'ia 
# (les vrais positifs, faux positifs, vrais négatifs et faux négatifs) par rapport aux
# données ayant été prédits par le modèle 
fig, ax = plt.subplots(figsize=(10, 10))
disp = ConfusionMatrixDisplay.from_estimator(
    model_agri_ai,
    X_test, # Données de test
    Y_test # Labels réels
)

disp.plot()

# Affichage du rapport complet pour évaluer 
# précision - rappel - score f1 - support
# des résultats prédit par le modèle
report = classification_report(Y_test, Y_pred)

print("BILAN DES PERFORMANCES :")
print(report)

# 6 - Sauvegarde du modèle entraîné
# joblib.dump() -> pour sauvegarder le modèle dans un fichier
# Le nom du fichier est "model_agri_ai.pkl" (pkl -> pickle -> format de sérialisation)
joblib.dump(model_agri_ai, "./save_data_agri_ai/model_agri_ai.pkl")

print("\nModèle sauvegardé avec succès sous le nom model_agri_ai.pkl")