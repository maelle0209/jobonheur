from flask import Flask, request, jsonify
from flask_cors import CORS
from chatterbot import ChatBot
from chatterbot.trainers import ChatterBotCorpusTrainer
import os
import sys

# Forcer UTF-8
sys.stdout.reconfigure(encoding='utf-8')

app = Flask(__name__)
CORS(app)  # Autorise les requêtes depuis ton site

# Création du chatbot
chatbot = ChatBot("JobBot")
trainer = ChatterBotCorpusTrainer(chatbot)

# Obtenir le chemin absolu du fichier work_advice.yml
chemin_yaml = os.path.abspath("work_advice.yml")
print("Chemin du fichier YAML :", chemin_yaml)

# Entraînement du chatbot avec le corpus de base en français et ton corpus personnalisé
trainer.train("chatterbot.corpus.french", chemin_yaml)

@app.route("/chat", methods=["POST"])
def chat():
    user_message = request.json.get("message")
    if not user_message:
        return jsonify({"response": "Erreur : message vide"}), 400
    bot_response = chatbot.get_response(user_message)
    return jsonify({"response": str(bot_response)})

if __name__ == "__main__":
    app.run(debug=True)
