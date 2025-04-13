from chatterbot import ChatBot

chatbot = ChatBot("JobBot", storage_adapter="chatterbot.storage.SQLStorageAdapter", database_uri="sqlite:///database.sqlite3")

# Vérifier les dialogues enregistrés
statements = list(chatbot.storage.filter())  # Convertir en liste


if statements:
    print("La base de données contient des conversations.")
    for statement in statements[:20]:  # Affiche les 10 premières
        print(f"Question : {statement.text} | Réponse : {statement.in_response_to}")
else:
    print(" Aucune donnée trouvée dans la base. Entraînez votre chatbot !")
 