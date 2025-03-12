import pandas as pd
import sys
import chardet
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.linear_model import LinearRegression
from sklearn.metrics import mean_squared_error
import matplotlib.pyplot as plt

# Forcer l'affichage UTF-8 pour éviter les erreurs de caractères spéciaux
sys.stdout.reconfigure(encoding='utf-8')

# **1. Fonction pour charger un CSV avec détection automatique d'encodage**
def load_csv(filepath):
    with open(filepath, 'rb') as f:
        result = chardet.detect(f.read())
    encoding = result['encoding']
    print(f"Encodage détecté : {encoding}")
    
    try:
        df = pd.read_csv(filepath, encoding=encoding, sep=";")
    except UnicodeDecodeError:
        print(f"Erreur avec {encoding}. Tentative avec ISO-8859-1.")
        df = pd.read_csv(filepath, encoding="ISO-8859-1", sep=";")
    return df

# **2. Charger les fichiers CSV**
chomage_path = "C:/MAMP/htdocs/jobonheur/bd_chomage/chomage.csv"
diplome_path = "C:/MAMP/htdocs/jobonheur/bd_chomage/diplome.csv"

df_chomage = load_csv(chomage_path)
df_diplome = load_csv(diplome_path)

# **3. Correction de l'encodage des colonnes**
df_chomage.rename(columns=lambda x: x.replace("", "Â"), inplace=True)
df_diplome.rename(columns=lambda x: x.strip(), inplace=True)  # Nettoyer les noms de colonnes

# **4. Transformer les données chômage en format long**
df_chomage = pd.melt(df_chomage, id_vars=["Sexe et Âge"], var_name="Trimestre", value_name="Taux")

# **5. Séparer "Sexe et Âge" en deux colonnes**
df_chomage[['Sexe', 'Âge']] = df_chomage['Sexe et Âge'].str.extract(r'(Femmes|Hommes|Ensemble)?\s*(.*)')
df_chomage['Sexe'] = df_chomage['Sexe'].fillna(method='ffill')  # Remplissage des valeurs vides

# **6. Convertir le taux en float**
df_chomage["Taux"] = df_chomage["Taux"].astype(str).str.replace(",", ".").astype(float)

# **7. Convertir les trimestres en format numérique (année + trimestre)**
df_chomage['Année'] = df_chomage['Trimestre'].str.extract(r'(\d{4})').astype(int)
df_chomage['Trimestre_Num'] = df_chomage['Trimestre'].str.extract(r'T(\d)')[0].astype(int)

# **8. Créer une colonne numérique pour la régression (ex: 2020.25 pour T1-2020)**
df_chomage['Trimestre_Décimal'] = df_chomage['Année'] + (df_chomage['Trimestre_Num'] - 1) / 4

# **9. Préparer les données pour la régression**
X = df_chomage[["Trimestre_Décimal"]]
y = df_chomage["Taux"]

# Séparation train/test (80%/20%)
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# **10. Appliquer un modèle de régression linéaire**
model = LinearRegression()
model.fit(X_train, y_train)

# **11. Faire des prédictions et évaluer le modèle**
y_pred = model.predict(X_test)
mse = mean_squared_error(y_test, y_pred)
print(f'Erreur quadratique moyenne : {mse:.4f}')

# **12. Visualisation des résultats**
plt.figure(figsize=(10, 6))
plt.scatter(X_test, y_test, color='blue', label='Données réelles')
plt.plot(X_test, y_pred, color='red', label='Prédictions', linewidth=2)
plt.xlabel('Trimestre (Année + Décimale)')
plt.ylabel('Taux de chômage (%)')
plt.title('Prédiction du Taux de Chômage')
plt.legend()
plt.show()

# **13. Prédiction pour un trimestre futur (ex: T1-2025)**
trimestre_futur = 2025 + (1 - 1) / 4  # T1-2025 => 2025.00
prediction_futur = model.predict([[trimestre_futur]])

print(f"Prédiction pour T1-2025 : {prediction_futur[0]:.2f}%")
