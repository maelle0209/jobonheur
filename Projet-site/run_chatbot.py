import sys
from chatterbot import ChatBot

# Forcer l'encodage UTF-8
sys.stdout.reconfigure(encoding='utf-8')

# Charger le chatbot
chatbot = ChatBot("JobBot")

# Boucle pour tester le chatbot
while True:
    user_input = input("Vous: ")
    if user_input.lower() in ["quit", "exit", "bye"]:
        print("JobBot: Au revoir !")
        break
    response = chatbot.get_response(user_input)
    print("JobBot:", str(response))
