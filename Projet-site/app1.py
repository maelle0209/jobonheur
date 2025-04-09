from flask import Flask, jsonify, request, session #pour créer l'application web.
import joblib # Pour charger le modèle entraîné
import pandas as pd # Pour structurer les données en DataFrame
import requests # Pour envoyer des requêtes HTTP
import numpy as np
from flask_cors import CORS #pour permettre l’accès à l’API depuis d'autres domaines (frontend et backend séparés).

# Initialisation de l'application Flask
app = Flask(__name__)
CORS(app)  # Active CORS pour toutes les routes
# Clé secrète pour gérer les sessions utilisateur
app.secret_key = 'ton_clé_secrète'  

def get_client_data(id_client):
    """
    Envoie une requête POST à un script PHP pour récupérer les données d'un client donné.

    Args:
        id_client (str): L'identifiant unique du client.

    Returns:
        dict: Un dictionnaire contenant les données du client.

    Raises:
        ValueError: Si l'API retourne une erreur ou une réponse invalide.
    """
    url = "http://localhost/jobonheur/Projet-site/getClientData.php"
    headers = {'Content-Type': 'application/json'}
    data = {'id_client': id_client}

    print(f"Envoi de la requête à {url} avec {data}")  # Debug

    response = requests.post(url, json=data, headers=headers)

    print(f"Réponse API PHP: {response.status_code} - {response.text}")  # Debug

    if response.status_code == 200:
        try:
            data = response.json()
            print(f"Données reçues de l'API : {data}")  # Debug
        except ValueError:
            raise ValueError("La réponse de l'API PHP n'est pas un JSON valide.")

        if "error" in data:
            raise ValueError(f"Erreur API: {data['error']}")

        return data
    else:
        raise ValueError(f"Erreur lors de la récupération des données (Code {response.status_code})")

# Charger le modèle
model, features_used = joblib.load("best_model.pkl")  # Décompresse bien les deux éléments
print(f"Features utilisées pour l'entraînement : {features_used}")  # Debug


# Vérifier si le modèle a des noms de colonnes
if hasattr(model, "feature_names_in_"):
    expected_features = model.feature_names_in_
    print(f"Features attendus par le modèle : {expected_features}")  # Debug

# Fonction pour générer des conseils
def generate_advice(predicted_rate):
     """
    Génère une liste de conseils personnalisés en fonction du taux de chômage prédit.

    Args:
        predicted_rate (float): Le taux de chômage prédit (entre 0 et 1).

    Returns:
        list: Une liste de conseils à afficher à l'utilisateur.
    """
    predicted_rate *= 100
    advice = []
    if predicted_rate > 12:
        advice.append("Le taux de chômage est élevé pour ce groupe. Envisage de poursuivre des études supplémentaires.")
        advice.append("Explore des secteurs porteurs comme la tech, la santé ou la finance.")
        advice.append("Développe des compétences en ligne (certifications, cours gratuits).")
    elif 5 < predicted_rate <= 10:
        advice.append("Le taux de chômage est modéré. Pense à te spécialiser dans un domaine en demande.")
        advice.append("Renforce ton réseau professionnel en participant à des événements.")
    else:
        advice.append("Le taux de chômage est faible pour ce groupe. Continue dans cette direction et optimise ton CV.")
    return advice

@app.route('/predict', methods=['POST'])
def predict():
    """
    Endpoint principal de prédiction.
    Reçoit un ID client, récupère ses données, génère une prédiction et retourne des conseils.

    Returns:
        JSON: Une réponse contenant la prédiction et les conseils ou un message d'erreur.
    """
    try:
        data = request.get_json()  
        print(f"Requête reçue : {data}")
        id_client = data.get('id_client')  
        print(f"ID Client reçu dans la requête : {id_client}")

        if not id_client:
            if 'client_id' in session:
                id_client = session['client_id']
                print(f"ID récupéré depuis la session : {id_client}")
            else:
                return jsonify({'error': "ID du client manquant"}), 400

        # Stocker l'ID client dans la session
        session['client_id'] = id_client
        print(f"ID Client dans la session : {session['client_id']}")

        # Récupération des données
        client_data = get_client_data(id_client)
        print(f"Client Data récupéré : {client_data}")

        if not client_data:
            return jsonify({'error': "Données client non trouvées"}), 400

        sexe = client_data['sexe'].lower()
        age_category = client_data['age_category']
        education_level = client_data['education_level']

        # Normalisation des catégories d'âge
        age_mapping = {
            "15_24": "15_24",
            "25_49": "25_49",
            "50_plus": "50_plus"
        }
        age_category = age_mapping.get(age_category, None)

        # Normalisation du niveau d'éducation
        education_mapping = {
            "baccalaureat": "baccalaureat",
            "bac_plus_2": "bac_plus_2",
            "aucun_diplome": "aucun_diplome",
            "cap_bep": "cap_bep"
        }
        education_level = education_mapping.get(education_level, None)

        # Vérification des valeurs
        if not age_category or not education_level:
            return jsonify({'error': "Valeurs d'entrée incorrectes"}), 400

        # Normalisation du sexe
        sexe = 1 if sexe == "homme" else 0  # Accepte aussi les majuscules/minuscules

        # Création du dictionnaire de features
        transformed_data = {
            'Valeur': 0,  # Remplir la valeur attendue pour 'Valeur'
            'Sexe_Hommes': sexe,
            'Age_15-24 ans': 1 if age_category == "15_24" else 0,
            'Age_25-49 ans': 1 if age_category == "25_49" else 0,
            'Age_50 ans ou plus': 1 if age_category == "50_plus" else 0,
            'Niveau de diplome_Bac + 2 ou plus': 1 if education_level == "bac_plus_2" else 0,
            'Niveau de diplome_Aucun diplome, brevet': 1 if education_level == "aucun_diplome" else 0,
            'Niveau de diplome_Baccalaureat': 1 if education_level == "baccalaureat" else 0,
            'Niveau de diplome_CAP, BEP': 1 if education_level == "cap_bep" else 0
        }

        # Création du DataFrame
        print(f"Transformed Data : {transformed_data}")
        input_data = pd.DataFrame([transformed_data])

        # Assurer la correspondance des features
        if hasattr(model, "feature_names_in_"):
            input_data = input_data[model.feature_names_in_]  # Filtrer selon les colonnes du modèle
        else:
            input_data = input_data.to_numpy()  # Transformer en array si nécessaire

        # Prédiction
        prediction = model.predict(input_data)[0]

        # Génération des conseils
        advice = generate_advice(prediction)

        return jsonify({'prediction': f"{round(prediction * 100, 6)}%", 'advice': advice})

    except Exception as e:
        # Gestion des erreurs internes
        return jsonify({'error': str(e)}), 500

# Lancement de l'application en mode debug pour le développement
if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)
