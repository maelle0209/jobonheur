from flask import Flask, request, jsonify
from flask_cors import CORS
from chatterbot import ChatBot
import sys
import joblib
import json
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity


# Forcer UTF-8
sys.stdout.reconfigure(encoding='utf-8')

app = Flask(__name__)
CORS(app)  # Autorise les requêtes depuis ton site

# Charger le chatbot
chatbot = ChatBot(
    "JobBot",
    storage_adapter="chatterbot.storage.SQLStorageAdapter",
    database_uri="sqlite:///database.sqlite3"
)

# Charger le modèle Naïve Bayes pour classifier les intentions
intent_model = joblib.load("intent_classifier.pkl")

# Charger le modèle TF-IDF
with open("tfidf_model.json", "r") as file:
    tfidf_data = json.load(file)

vectorizer = TfidfVectorizer()
tfidf_matrix = np.array(tfidf_data["tfidf_matrix"])
vectorizer.fit(tfidf_data["questions"])


# Fonction pour trouver la meilleure réponse avec TF-IDF
def get_best_response(user_input):
    user_tfidf = vectorizer.transform([user_input])
    similarities = cosine_similarity(user_tfidf, tfidf_matrix)
    best_match_idx = np.argmax(similarities)

    if similarities[0][best_match_idx] < 0.5:  # Seuil de confiance
        return "Je ne suis pas sûr de comprendre, peux-tu reformuler ?"
    
    best_question = tfidf_data["questions"][best_match_idx]
    return chatbot.get_response(best_question)

# Fonction pour détecter l'intention d'un message
def detect_intent(user_input):
    return intent_model.predict([user_input])[0]

@app.route("/chat", methods=["POST"])
def chat():
    user_message = request.json.get("message")
    if not user_message:
        return jsonify({"response": "Erreur : message vide"}), 400

    # Classification de l'intention
    intent = detect_intent(user_message)

    # Si l'intention est "salutation" ou "au_revoir", répondre directement
    if intent == "salutation":
        return jsonify({"response": "Bonjour ! Comment puis-je vous aider ?"})
    elif intent == "au_revoir":
        return jsonify({"response": "Au revoir ! Passez une bonne journée 😊"})

    # Chercher la meilleure réponse avec TF-IDF
    bot_response = get_best_response(user_message)
    return jsonify({"response": str(bot_response)})

if __name__ == "__main__":
    app.run(debug=True)