import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity
import mysql.connector

conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="root",  # Mets ici ton mot de passe MySQL
    database="jobonheur"
)

cursor = conn.cursor(dictionary=True)

# 1️⃣ Récupérer les offres en favoris de l'utilisateur
user_id = 1  # Remplace avec l'ID de l'utilisateur connecté
cursor.execute("""
    SELECT r.job_id, r.`Intitulé du poste`, r.`compétences exigées`, r.`Type de contrat`, r.`niveau libellé`
    FROM favoris f
    JOIN response_2 r ON f.job_id = r.job_id
    WHERE f.user_id = %s
""", (user_id,))
favoris = cursor.fetchall()

# 2️⃣ Récupérer toutes les offres pour comparaison
cursor.execute("""
    SELECT job_id, `Intitulé du poste`, `compétences exigées`, `Type de contrat`, `niveau libellé`
    FROM response_2
""")
offres = cursor.fetchall()

# Convertir en DataFrame
df_offres = pd.DataFrame(offres)

# 3️⃣ Combiner les colonnes en une seule pour l'analyse textuelle
df_offres["text"] = df_offres["compétences exigées"] + " " + df_offres["Type de contrat"] + " " + df_offres["niveau libellé"]

# 4️⃣ Appliquer TF-IDF pour transformer les textes en vecteurs
vectorizer = TfidfVectorizer(stop_words=None)
tfidf_matrix = vectorizer.fit_transform(df_offres["text"].fillna(""))

# 5️⃣ Calculer la similarité entre les offres en favoris et toutes les autres
similarites = cosine_similarity(tfidf_matrix)

# 6️⃣ Trouver les offres les plus proches des favoris
recommandations = []
for favori in favoris:
    job_index = df_offres[df_offres["job_id"] == favori["job_id"]].index[0]
    scores = list(enumerate(similarites[job_index]))
    scores = sorted(scores, key=lambda x: x[1], reverse=True)[1:6]  # Prendre les 5 offres les plus similaires

    for idx, score in scores:
        recommandations.append((df_offres.loc[idx, "job_id"], score))

# Trier les recommandations par score décroissant
recommandations = sorted(recommandations, key=lambda x: x[1], reverse=True)

# Afficher les recommandations
for job_id, score in recommandations:
    print(f"Offre recommandée: Job ID {job_id} - Score de similarité: {score:.2f}")

# Fermer la connexion
cursor.close()
conn.close()
