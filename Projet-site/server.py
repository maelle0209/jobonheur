from flask import Flask, render_template, send_from_directory, request
import os

app = Flask(__name__)

# Dossier contenant les figures générées
FIGURES_FOLDER = os.path.join("numpy-statistiques", "figures")

@app.route('/')
def home():
    return render_template("statistiques.html")

@app.route('/get_figure')
def get_figure():
    stat = request.args.get("stat")
    if stat == "sexe":
        filename = "sexe.png"
    elif stat == "age":
        filename = "age.png"
    elif stat == "diplome":
        filename = "niveau de diplome.png"
    else:
        return "Statistique non trouvée", 404

    return send_from_directory(FIGURES_FOLDER, filename)

if __name__ == "__main__":
    app.run(debug=True)
