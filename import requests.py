²import requests
import time
import sqlite3

# Configuration de l'API
API_URL = "https://api.francetravail.io/partenaire/offresdemploi/v2/offres/search"
ACCESS_TOKEN = "HagfRi__vAxP4H5nWxOH_BS43dc"
INTERVAL = 300  # Intervalle de 300 secondes (5 minutes) entre les requêtes

# Configuration de la base de données SQLite
conn = sqlite3.connect('jobhoneur.db')
cursor = conn.cursor()

# Créer une table si elle n'existe pas
cursor.execute('''
    CREATE TABLE IF NOT EXISTS api_data (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        data TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )
''')
conn.commit()

def fetch_and_store():
    headers = {
        'Authorization': f'Bearer {ACCESS_TOKEN}'
    }

    try:
        response = requests.get(API_URL, headers=headers)
        if response.status_code == 200:
            data = response.json()
            # Sauvegarder les données dans la base de données
            cursor.execute('INSERT INTO api_data (data) VALUES (?)', (str(data),))
            conn.commit()
            print("Données insérées avec succès.")
        else:
            print(f"Erreur {response.status_code}: {response.text}")
    except requests.exceptions.RequestException as e:
        print(f"Erreur lors de la requête : {e}")

# Boucle de collecte continue
while True:
    fetch_and_store()
    time.sleep(INTERVAL)  # Attendre avant la prochaine requête

