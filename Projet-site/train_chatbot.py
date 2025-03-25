import os
import json
import yaml
import numpy as np
from chatterbot import ChatBot
from chatterbot.trainers import ChatterBotCorpusTrainer
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import sys
sys.stdout.reconfigure(encoding='utf-8')
# Création du chatbot avec stockage en SQLite
chatbot = ChatBot(
    "JobBot",
    storage_adapter="chatterbot.storage.SQLStorageAdapter",
    database_uri="sqlite:///database.sqlite3"
)


# Entraînement avec des données de base et un corpus personnalisé
trainer = ChatterBotCorpusTrainer(chatbot)

# Définir le chemin absolu du fichier YAML personnalisé
# Charger les données depuis le fichier YAML
chemin_yaml = "work_advice.yml"
with open(chemin_yaml, "r", encoding="utf-8") as file:
    data = yaml.load(file, Loader=yaml.FullLoader)

# Ajouter les conversations YAML à la base de données de ChatterBot
for conversation in data['conversations']:
    if len(conversation) >= 2:  # Vérifier qu'il y a bien une question et une réponse
        chatbot.storage.create(text=conversation[0], in_response_to=conversation[1])

print("✅ Les conversations ont été ajoutées à la base de données.")

def load_conversations():
    statements = chatbot.storage.filter()
    
    if not statements:  # Si la base est vide
        print("⚠️ Aucun dialogue trouvé ! Veuillez entraîner votre chatbot d'abord.")
        return {}

    return {str(statement.text): str(statement.in_response_to) for statement in statements if statement.in_response_to}

# Charger toutes les conversations du bot
#def load_conversations():
 #   statements = chatbot.storage.filter()
  #  return {str(statement.text): str(statement.response) for statement in statements}

# Construire le modèle TF-IDF
conversations = load_conversations()

# Extraire les questions depuis les données YAML (extrait chaque première entrée de chaque conversation)
questions = [conversation[0] for conversation in data['conversations'] if len(conversation[0].strip()) > 0]
# Assurer que les réponses existent également pour chaque question
answers = [conversation[1] for conversation in data['conversations']]

# Initialiser le TfidfVectorizer
vectorizer = TfidfVectorizer()

try:
    # Créer la matrice TF-IDF pour les questions
    tfidf_matrix = vectorizer.fit_transform(questions)
    print("TF-IDF matrix créé avec succes")
except ValueError as e:
    print(f"Erreur lors de la creation de la matrice TF-IDF : {e}")

# Fonction d'amélioration des réponses avec TF-IDF
def get_best_response(user_input):
    user_tfidf = vectorizer.transform([user_input])
    similarities = cosine_similarity(user_tfidf, tfidf_matrix)
    best_match_idx = np.argmax(similarities)

    if similarities[0][best_match_idx] < 0.5:  # Seuil de confiance
        return "Je ne suis pas sûr de comprendre, peux-tu reformuler ?"
    
    best_question = questions[best_match_idx]
    return answers[best_match_idx]

# Sauvegarde du modèle TF-IDF avec encodage utf-8
with open("tfidf_model.json", "w", encoding="utf-8") as file:
    json.dump({"tfidf_matrix": tfidf_matrix.toarray().tolist(), "questions": questions, "answers": answers}, file)

# Affichage avec encodage UTF-8
print(" Modele TF-IDF sauvegarde !".encode('utf-8').decode())
