import json
import os
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.pipeline import make_pipeline
from sklearn.model_selection import train_test_split

# Charger les données d'entraînement des intentions
if not os.path.exists("intent_data.json"):
    print(" Fichier d'intentions introuvable ! Ajoutez `intent_data.json` avec des exemples.")
    exit()

with open("intent_data.json", "r") as file:
    data = json.load(file)

# Préparer les données pour l'entraînement
texts = []
labels = []
for intent, examples in data.items():
    for text in examples:
        texts.append(text)
        labels.append(intent)

# Création du modèle Naïve Bayes
model = make_pipeline(TfidfVectorizer(), MultinomialNB())

# Entraînement du modèle
X_train, X_test, y_train, y_test = train_test_split(texts, labels, test_size=0.2, random_state=42)
model.fit(X_train, y_train)

# Sauvegarde du modèle
import joblib
joblib.dump(model, "intent_classifier.pkl")
print("Modele d'intention Naïve Bayes sauvegarde !")
