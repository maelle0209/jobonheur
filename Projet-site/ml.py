import pandas as pd
import chardet

# Détecter l'encodage du fichier 'chomage.csv'
with open('C:/MAMP/htdocs/jobonheur/bd_chomage/chomage.csv', 'rb') as file:
    result_chomage = chardet.detect(file.read())
    print(f"Encodage détecté pour chomage.csv: {result_chomage['encoding']}")

# Lire le fichier 'chomage.csv' avec l'encodage détecté
with open('C:/MAMP/htdocs/jobonheur/bd_chomage/chomage.csv', mode='r', encoding=result_chomage['encoding']) as file:
    chomage_content = file.read()

# Réécrire le fichier 'chomage.csv' en UTF-8
with open('C:/MAMP/htdocs/jobonheur/bd_chomage/chomage.csv', mode='w', encoding='utf-8') as file:
    file.write(chomage_content)

# Charger le fichier 'chomage.csv' avec Pandas
chomage_data = pd.read_csv("C:/MAMP/htdocs/jobonheur/bd_chomage/chomage.csv", sep=";")

# Vérifier les colonnes du fichier 'chomage.csv'
print("Colonnes dans chomage.csv :")
print(chomage_data.columns)

# Renommer les colonnes si nécessaire pour corriger les erreurs d'encodage
chomage_data.columns = chomage_data.columns.str.replace("Sexe et ge", "Sexe et age")
chomage_data = chomage_data.rename(columns={"P▒riode": "Période"})

# Appliquer melt pour transformer les données
df_melted = chomage_data.melt(id_vars=["Sexe et age"], var_name="Période", value_name="Valeur")

# Nettoyer les espaces au début et à la fin
df_melted["Sexe et age"] = df_melted["Sexe et age"].str.strip()

# Liste des tranches d'âge possibles
age_categories = ["15-24 ans", "25-49 ans", "50 ans ou plus"]

# Créer des colonnes 'Sexe' et 'Age' vides
df_melted["Sexe"] = None
df_melted["Age"] = None

# Variables pour suivre la dernière valeur de Sexe et Age
last_sex = None

# Boucle pour reconstruire les colonnes 'Sexe' et 'Age' correctement
for index, row in df_melted.iterrows():
    value = row["Sexe et age"]
    
    # Si c'est "Femmes" ou "Hommes", on met à jour `last_sex`
    if value in ["Femmes", "Hommes"]:
        last_sex = value
    elif value in age_categories:  # Si c'est une tranche d'âge, on garde le dernier sexe trouvé
        df_melted.at[index, "Sexe"] = last_sex
        df_melted.at[index, "Age"] = value

# Supprimer les lignes où 'Sexe' ou 'Age' est vide
df_melted = df_melted.dropna(subset=["Sexe", "Age"])

# Supprimer la colonne d'origine
df_melted = df_melted.drop(columns=["Sexe et age"])

# Convertir la colonne "Valeur" en numérique
df_melted["Valeur"] = pd.to_numeric(df_melted["Valeur"], errors="coerce")

# Extraire l'année de la colonne "Période" et l'ajouter à une nouvelle colonne "Année"
df_melted["Année"] = df_melted["Période"].str[:4].astype(int)

# Grouper par Année, Sexe et Age, puis calculer la moyenne des valeurs
df_yearly = df_melted.groupby(["Année", "Sexe", "Age"])["Valeur"].mean().reset_index()

# Afficher les résultats
print("Données de chômage (années, sexe, âge) :")
print(df_yearly.head(10000))

# Détecter l'encodage du fichier 'diplome.csv'
with open('C:/MAMP/htdocs/jobonheur/bd_chomage/diplome.csv', 'rb') as file:
    result_diplome = chardet.detect(file.read())
    print(f"Encodage détecté pour diplome.csv: {result_diplome['encoding']}")

# Lire le fichier 'diplome.csv' avec l'encodage détecté
with open('C:/MAMP/htdocs/jobonheur/bd_chomage/diplome.csv', mode='r', encoding=result_diplome['encoding']) as file:
    diplome_content = file.read()

# Réécrire le fichier 'diplome.csv' en UTF-8
with open('C:/MAMP/htdocs/jobonheur/bd_chomage/diplome.csv', mode='w', encoding='utf-8') as file:
    file.write(diplome_content)

# Charger le fichier 'diplome.csv' avec Pandas
diplome_data = pd.read_csv("C:/MAMP/htdocs/jobonheur/bd_chomage/diplome.csv", sep=";")


# Renommer la colonne 'AnnÃ©e' en 'Année'
diplome_data.columns = ['Niveau de diplome', 'Taux', 'Année']


# Filtrer les données de chômage pour l'année 2023
chomage_2023 = df_yearly[df_yearly['Année'] == 2023]

# Fusionner les deux datasets sur l'année et le diplôme
merged_data_2023 = pd.merge(chomage_2023, diplome_data, on=["Année"], how="inner")

# Afficher les résultats fusionnés
print("Données fusionnées (chômage et diplôme pour 2023) :")
print(merged_data_2023.head(100000))
# Sauvegarder les données fusionnées dans un fichier CSV avec un encodage UTF-8
merged_data_2023.to_csv('C:/MAMP/htdocs/jobonheur/bd_chomage/merged_data_2023.csv', index=False, encoding='utf-8')

print("Les données fusionnées ont été sauvegardées dans 'merged_data_2023.csv'")
