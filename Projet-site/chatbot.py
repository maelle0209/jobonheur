from chatterbot import ChatBot
from chatterbot.trainers import ChatterBotCorpusTrainer
import sys
sys.stdout.reconfigure(encoding='utf-8')


# Création du chatbot
chatbot = ChatBot("JobBot")

# Entraînement avec des données de base
trainer = ChatterBotCorpusTrainer(chatbot)
trainer.train("chatterbot.corpus.french")

# Tester le chatbot
while True:
    user_input = input("Vous: ")
    if user_input.lower() in ["quit", "exit", "bye"]:
        print("JobBot: Au revoir !")
        break
    response = chatbot.get_response(user_input)
    print("JobBot:", response)
