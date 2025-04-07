import pandas as pd
from sklearn.model_selection import train_test_split, GridSearchCV, cross_val_score
from sklearn.linear_model import LinearRegression, Ridge
from sklearn.metrics import mean_squared_error, r2_score, mean_absolute_error
from sklearn.ensemble import RandomForestRegressor, GradientBoostingRegressor
from sklearn.preprocessing import StandardScaler
import matplotlib.pyplot as plt
import joblib
import xgboost as xgb
import numpy as np

import pandas as pd

# Chemin du fichier CSV
file_path = '../bd_chomage/dataset_chomage.csv'

# Charger le fichier CSV en gérant les guillemets et les séparateurs
df = pd.read_csv(file_path, delimiter=',', quotechar='"')

# Afficher les premières lignes pour vérifier le contenu
print(df.head())

# Nettoyer les noms de colonnes pour éviter les espaces invisibles
df.columns = df.columns.str.strip()

# Si des colonnes ont des espaces en trop, on peut les nettoyer comme suit
df['Niveau de diplome'] = df['Niveau de diplome'].str.replace('  ', ' ', regex=True)

# Enregistrer le fichier nettoyé
df.to_csv('C:/MAMP/htdocs/jobonheur/bd_chomage/dataset_chomage_clean.csv', index=False)

# Afficher les premières lignes du fichier nettoyé pour vérifier
print("Le fichier nettoyé est enregistré sous 'dataset_chomage_clean.csv'.")
print(df.head())


# Remplacer les virgules par des points et convertir en float
df['Taux de chômage'] = df['Taux de chômage'].replace(',', '.', regex=True).astype(float) / 100

# Encoder les variables catégorielles
df = pd.get_dummies(df, columns=['Sexe', 'Age', 'Niveau de diplome'], drop_first=False)

# Remplir les valeurs manquantes
df.fillna(df.mean(numeric_only=True), inplace=True)  # Colonnes numériques
for col in df.select_dtypes(include=['object']).columns:
    df[col].fillna(df[col].mode()[0], inplace=True)  # Colonnes catégorielles

# Définir les variables indépendantes et la variable cible
X = df.drop(['Année', 'Taux de chômage'], axis=1)
y = df['Taux de chômage']
# Vérifier les features présentes dans X
print(f"Features présentes dans X: {list(X.columns)}")
print(f"Nombre de features dans X: {X.shape[1]}")
# Division en train/test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
print(X_train.columns)
print(X_test.columns)
# S'assurer que X_train et X_test ont les mêmes colonnes
X_test = X_test.reindex(columns=X_train.columns, fill_value=0)

# Ajouter du bruit aux données pour éviter un sur-apprentissage
X_train_noisy = X_train + np.random.normal(0, 0.1, X_train.shape)
X_test_noisy = X_test + np.random.normal(0, 0.1, X_test.shape)

# Appliquer la standardisation UNIQUEMENT aux modèles qui en ont besoin
scaler = StandardScaler()
X_train_scaled = scaler.fit_transform(X_train_noisy)
X_test_scaled = scaler.transform(X_test_noisy)

# ------------------------- Régression Linéaire -------------------------
model_lr = LinearRegression()
model_lr.fit(X_train_scaled, y_train)
y_pred_lr = model_lr.predict(X_test_scaled)

# Calculer les métriques
mse_lr = mean_squared_error(y_test, y_pred_lr)
mae_lr = mean_absolute_error(y_test, y_pred_lr)
r2_lr = r2_score(y_test, y_pred_lr)
print(f'Erreur quadratique moyenne (Régression Linéaire) : {mse_lr}')
print(f'Erreur Absolue Moyenne (Régression Linéaire) : {mae_lr}')
print(f'R² (Régression Linéaire) : {r2_lr}')

# ------------------------- Ridge -------------------------
ridge_model = Ridge(alpha=1.0)
ridge_model.fit(X_train_scaled, y_train)
ridge_y_pred = ridge_model.predict(X_test_scaled)

mse_ridge = mean_squared_error(y_test, ridge_y_pred)
mae_ridge = mean_absolute_error(y_test, ridge_y_pred)
r2_ridge = r2_score(y_test, ridge_y_pred)
print(f'Erreur quadratique moyenne (Ridge) : {mse_ridge}')
print(f'Erreur Absolue Moyenne (Ridge) : {mae_ridge}')
print(f'R² (Ridge) : {r2_ridge}')

# ------------------------- Forêt Aléatoire -------------------------
param_grid_rf = {'n_estimators': [50, 100, 150], 'max_depth': [3, 5, 10]}
grid_search_rf = GridSearchCV(RandomForestRegressor(), param_grid_rf, cv=5, scoring='neg_mean_squared_error')
grid_search_rf.fit(X_train_scaled, y_train)
rf_model = grid_search_rf.best_estimator_

rf_y_pred = rf_model.predict(X_test_scaled)
rf_mse = mean_squared_error(y_test, rf_y_pred)
rf_mae = mean_absolute_error(y_test, rf_y_pred)
rf_r2 = r2_score(y_test, rf_y_pred)
print(f'Erreur quadratique moyenne (Forêt Aléatoire) : {rf_mse}')
print(f'Erreur Absolue Moyenne (Forêt Aléatoire) : {rf_mae}')
print(f'R² (Forêt Aléatoire) : {rf_r2}')

# ------------------------- Gradient Boosting -------------------------
gb_model = GradientBoostingRegressor(n_estimators=50, max_depth=3, random_state=42)
gb_model.fit(X_train_scaled, y_train)
gb_y_pred = gb_model.predict(X_test_scaled)

gb_mse = mean_squared_error(y_test, gb_y_pred)
gb_mae = mean_absolute_error(y_test, gb_y_pred)
gb_r2 = r2_score(y_test, gb_y_pred)
print(f'Erreur quadratique moyenne (Gradient Boosting) : {gb_mse}')
print(f'Erreur Absolue Moyenne (Gradient Boosting) : {gb_mae}')
print(f'R² (Gradient Boosting) : {gb_r2}')

# ------------------------- XGBoost -------------------------
xgb_model = xgb.XGBRegressor(n_estimators=50, max_depth=3, learning_rate=0.1)
xgb_model.fit(X_train_scaled, y_train)
xgb_y_pred = xgb_model.predict(X_test_scaled)

xgb_mse = mean_squared_error(y_test, xgb_y_pred)
xgb_mae = mean_absolute_error(y_test, xgb_y_pred)
xgb_r2 = r2_score(y_test, xgb_y_pred)
print(f'Erreur quadratique moyenne (XGBoost) : {xgb_mse}')
print(f'Erreur Absolue Moyenne (XGBoost) : {xgb_mae}')
print(f'R² (XGBoost) : {xgb_r2}')

# ------------------------- Comparaison des performances -------------------------
models = ['Régression Linéaire', 'Ridge', 'Forêt Aléatoire', 'Gradient Boosting', 'XGBoost']
errors = [mse_lr, mse_ridge, rf_mse, gb_mse, xgb_mse]

plt.bar(models, errors, color=['blue', 'green', 'red', 'orange', 'purple'])
plt.ylabel('Erreur quadratique moyenne')
plt.title('Comparaison des performances des modèles')
plt.show()

# ------------------------- Enregistrer le meilleur modèle -------------------------
best_model = min([(mse_lr, model_lr), (mse_ridge, ridge_model), (rf_mse, rf_model), (gb_mse, gb_model), (xgb_mse, xgb_model)], key=lambda x: x[0])[1]
features_used = X_train.columns.tolist()
print(f"Features utilisées pour l'entraînement : {features_used}")
def plot_predictions(y_test, y_pred, model_name):
    plt.figure(figsize=(6, 6))
    plt.scatter(y_test, y_pred, color='blue', edgecolor='k', alpha=0.6)
    plt.plot([y_test.min(), y_test.max()], [y_test.min(), y_test.max()], color='red', lw=2, linestyle='--')
    plt.xlabel('Valeurs réelles')
    plt.ylabel('Valeurs prédites')
    plt.title(f'{model_name} : Prédites vs Réelles')
    plt.grid(True)
    plt.tight_layout()
    plt.show()

# Exemple d'utilisation pour chaque modèle :
plot_predictions(y_test, y_pred_lr, 'Régression Linéaire')
plot_predictions(y_test, ridge_y_pred, 'Ridge')
plot_predictions(y_test, rf_y_pred, 'Forêt Aléatoire')
plot_predictions(y_test, gb_y_pred, 'Gradient Boosting')
plot_predictions(y_test, xgb_y_pred, 'XGBoost')
def plot_residuals(y_test, y_pred, model_name):
    residuals = y_test - y_pred
    plt.figure(figsize=(6, 4))
    plt.hist(residuals, bins=30, color='skyblue', edgecolor='black')
    plt.axvline(0, color='red', linestyle='--')
    plt.title(f'{model_name} : Histogramme des résidus')
    plt.xlabel('Résidus')
    plt.ylabel('Fréquence')
    plt.grid(True)
    plt.tight_layout()
    plt.show()

# Utilisation :
plot_residuals(y_test, y_pred_lr, 'Régression Linéaire')
plot_residuals(y_test, ridge_y_pred, 'Ridge')
plot_residuals(y_test, rf_y_pred, 'Forêt Aléatoire')
plot_residuals(y_test, gb_y_pred, 'Gradient Boosting')
plot_residuals(y_test, xgb_y_pred, 'XGBoost')
def plot_regression_line(y_test, y_pred, model_name):
    plt.figure(figsize=(6, 5))
    plt.scatter(range(len(y_test)), y_test, label='Valeurs réelles', alpha=0.6)
    plt.plot(range(len(y_pred)), y_pred, color='red', label='Valeurs prédites')
    plt.title(f'{model_name} : Régression - Réel vs Prédit')
    plt.xlabel('Index')
    plt.ylabel('Taux de chômage')
    plt.legend()
    plt.grid(True)
    plt.tight_layout()
    plt.show()

# Utilisation :
plot_regression_line(y_test.values, y_pred_lr, 'Régression Linéaire')
plot_regression_line(y_test.values, ridge_y_pred, 'Ridge')
plot_regression_line(y_test.values, rf_y_pred, 'Forêt Aléatoire')
plot_regression_line(y_test.values, gb_y_pred, 'Gradient Boosting')
plot_regression_line(y_test.values, xgb_y_pred, 'XGBoost')

# Sauvegarder également les features
#joblib.dump((best_model, features_used), 'best_model.pkl')
print("Modèle et features sauvegardés.")