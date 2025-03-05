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

# Charger les données
df = pd.read_csv('C:/MAMP/htdocs/jobonheur/bd_chomage/merged_data_2023.csv')

# Remplacer les virgules par des points et convertir en float
df['Taux'] = df['Taux'].replace(',', '.', regex=True).astype(float) / 100

# Encoder les variables catégorielles
df = pd.get_dummies(df, columns=['Sexe', 'Age', 'Niveau de diplome'], drop_first=True)

# Remplir les valeurs manquantes
df.fillna(df.mean(numeric_only=True), inplace=True)  # Colonnes numériques
for col in df.select_dtypes(include=['object']).columns:
    df[col].fillna(df[col].mode()[0], inplace=True)  # Colonnes catégorielles

# Définir les variables indépendantes et la variable cible
X = df.drop(['Année', 'Taux'], axis=1)
y = df['Taux']

# Division en train/test
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

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
joblib.dump(best_model, 'best_model.pkl')
print("Modèle avec la meilleure performance sauvegardé.")
