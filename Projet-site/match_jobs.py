import mysql.connector
import pandas as pd
import nltk
from nltk.corpus import stopwords
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import sys
import json



# Connexion à la base de données
def connect_db():

    #Établit une connexion à la base de données MySQL.
    #Retourne :
    #    mysql.connector.connection_cext.CMySQLConnection: objet de connexion.
    
    return mysql.connector.connect(
        host="localhost",
        user="root",
        password="root",
        database="jobonheur"
    )

# Préparation des ressources linguistiques
# Télécharger les stopwords en français si tu ne l'as pas déjà fait
nltk.download('stopwords')
stop_words_french = stopwords.words('french')

# Récupération des offres d'emploi depuis la BDD
def get_jobs():

    #    Récupère toutes les offres d'emploi depuis la table response_2.
    #Retourne :
    #    list[dict]: liste des offres avec leurs champs pertinents
    db = connect_db()
    cursor = db.cursor(dictionary=True)
    cursor.execute("SELECT job_id, `compétences exigées`, `Intitulé du poste`, `Description` FROM response_2")
    jobs = cursor.fetchall()
    db.close()
    return jobs

# Récupération des compétences du client depuis la BDD
def get_client_skills(user_id):

    # Récupère les informations du CV du client depuis la base de données.
    
    #Paramètre :
    #    user_id (int): ID du client.
    
    #Retourne :
    #    str: Texte concaténé des informations du client.

    db = connect_db()
    cursor = db.cursor(dictionary=True)
    cursor.execute("SELECT title, education_level, education, experience, skills, languages FROM cv WHERE id_client = %s", (user_id,))
    client = cursor.fetchone()
    db.close()

    if not client:
        return ""
    
    # Concaténer toutes les informations du client pour l'analyse
    client_text = f"{client['title']} {client['education_level']} {client['education']} {client['experience']} {client['skills']} {client['languages']}"
    
    return client_text  

# Calcul de la similarité entre le CV et les offres
def find_best_matches(user_id, top_n=100):

    #Calcule les offres les plus pertinentes pour un client donné en comparant 
    #son profil aux descriptions d'offres à l’aide du modèle TF-IDF et de la similarité cosinus.

    #Paramètres :
    #    user_id (int): ID du client.
    #    top_n (int): Nombre de recommandations à retourner.

    #Retourne :
    #    list[dict]: Liste des offres les plus pertinentes avec ID et score de similarité.
    
    jobs = get_jobs()
    client_profile = get_client_skills(user_id)

    # Si aucune donnée client ou job, on retourne une liste vide
    if not jobs or not client_profile:
        return []

    # Construction des textes pour le modèle TF-IDF
    job_texts = [f"{job['Intitulé du poste']} {job['compétences exigées']} {job['Description']}" for job in jobs]
    job_ids = [job['job_id'] for job in jobs]

    # Initialisation du modèle TF-IDF
    vectorizer = TfidfVectorizer(stop_words=stop_words_french)
    tfidf_matrix = vectorizer.fit_transform([client_profile] + job_texts)

    # Calcul de la similarité cosinus entre le CV et les offres
    similarities = cosine_similarity(tfidf_matrix[0:1], tfidf_matrix[1:]).flatten()

    # Récupérer les offres les plus pertinentes
    sorted_indices = similarities.argsort()[::-1][:top_n]
    best_jobs = [{"job_id": job_ids[i], "score": similarities[i]} for i in sorted_indices]

    return best_jobs

