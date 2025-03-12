from flask import Flask, render_template, send_from_directory
import os

app = Flask(__name__)

# Dossier où sont stockées les figures
FIGURE_FOLDER = "numpy-statistiques/figures"

@app.route("/")
def index():
    return f"""
    <h1>Analyse du Chômage</h1>
    <h2>Répartition par Âge</h2>
    <img src='/figures/age.png' width='400'>
    
    <h2>Répartition par Sexe</h2>
    <img src='/figures/sexe.png' width='400'>
    
    <h2>Répartition par Niveau de Diplôme</h2>
    <img src='/figures/niveau de diplome.png' width='400'>
    """

# Route pour afficher les images des statistiques
@app.route("/figures/<filename>")
def get_figure(filename):
    return send_from_directory(FIGURE_FOLDER, filename)

if __name__ == "__main__":
    app.run(debug=True)
