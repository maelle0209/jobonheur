import os
from chatterbot import ChatBot
from chatterbot.trainers import ChatterBotCorpusTrainer

# Création du chatbot avec un adapter de logique personnalisé
chatbot = ChatBot(
    "JobBot",
    logic_adapters=[
        {
            "import_path": "chatterbot.logic.BestMatch",
            "default_response": "Je suis désolé, je ne sais pas comment répondre à cela pour le moment.",
            "maximum_similarity_threshold": 0.90
        }
    ]
)

# Entraînement avec des données de base et un corpus personnalisé
trainer = ChatterBotCorpusTrainer(chatbot)

# Définir le chemin absolu du fichier YAML personnalisé
chemin_yaml = os.path.abspath("work_advice.yml")  # Génère un chemin absolu correct

print("Chemin du fichier YAML :", chemin_yaml)  # Debugging pour vérifier le chemin

# Entraînement du chatbot
trainer.train(
    "chatterbot.corpus.french",  # Le corpus de base en français
    chemin_yaml                 # Utilisation du chemin absolu pour votre fichier personnalisé
)
