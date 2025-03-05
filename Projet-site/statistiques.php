<?php
// Si des données doivent être traitées au niveau du serveur, fais-le ici
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Code pour traiter les données du formulaire, par exemple, récupérer l'ID du client
    $clientId = $_POST['id_client'];
    
    // Simulation d'une réponse de prédiction
    $prediction = "Taux de chômage prévu : 8%"; // Exemple de prédiction
    $advice = ["Conseil : Formez-vous dans un secteur en forte demande", "Conseil : Restez flexible"];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques - Jobonheur</title>
    <link rel="stylesheet" href="styles/statistiques.css">
</head>
<body>

    <!-- Bouton de retour à la page précédente -->
    <div class="header">
        <a href="section.php" class="btn-retour">Retour à l'accueil</a>
    </div>

    <!-- Présentation des statistiques -->
    <div class="presentation">
        <h2>Les statistiques du chômage en France selon l'INSEE</h2>
        <p>Les données suivantes reflètent l'évolution du chômage en France, avec une analyse détaillée selon différents critères. Ces informations sont mises à jour régulièrement par l'INSEE afin de mieux comprendre les tendances du marché du travail.</p>
    </div>

    <!-- Formulaire de prédiction -->
    <div class="prediction-section">
        <h2>Tester la prédiction du chômage</h2>
        <form id="prediction-form" method="POST">
            <label for="id_client">Client ID :</label>
            <input type="number" id="id_client" name="id_client" required>
            <button class="btn-option" type="submit">Prédire</button>
        </form>
        
        <?php
        // Si la prédiction a été générée, l'afficher ici
        if (isset($prediction)) {
            echo "<div id='prediction-result' class='prediction-result'>";
            echo "<p>Prédiction : " . $prediction . "</p>";
            echo "<ul>";
            foreach ($advice as $item) {
                echo "<li>" . $item . "</li>";
            }
            echo "</ul></div>";
        }
        ?>
    </div>

    <!-- Section des options de filtrage des statistiques -->
    <div class="option-section">
        <button class="btn-option" onclick="showStat('sexe')">Statistiques par Sexe</button>
        <button class="btn-option" onclick="showStat('age')">Statistiques par Âge</button>
        <button class="btn-option" onclick="showStat('diplome')">Statistiques par Diplôme</button>
    </div>

    <!-- Section pour afficher les figures et les interprétations -->
    <div id="stat-result" class="stat-result">
        <img src="" alt="statistiques" id="stat-image" style="display: none;" />
        <div class="stat-interpretation" id="stat-interpretation">
            <p>Sélectionnez une option pour afficher les statistiques associées et leur interprétation.</p>
        </div>
    </div>

    <script>
        function showStat(option) {
            const resultDiv = document.getElementById('stat-result');
            let graphs = [];
            let interpretationText = "";
    
            if (option === "sexe") {
                graphs = [
                    "graphs/graphique_sexe1975.html",
                    "graphs/graphique_sexe1995.html",
                    "graphs/graphique_sexe2000.html",
                    "graphs/graphique_sexe2017.html",
                    "graphs/graphique_sexe.html"
                ];
                interpretationText = "Cette statistique montre les taux de chômage en fonction du sexe.";
            } else if (option === "age") {
                graphs = ["graphs/graphique_age.html"];
                interpretationText = "Cette statistique illustre les taux de chômage selon les tranches d’âge.";
            } else if (option === "diplome") {
                graphs = ["graphs/graphique_diplome.html"];
                interpretationText = "Cette statistique analyse l’impact du niveau de diplôme sur le chômage.";
            }
    
            // Générer plusieurs iframes si plusieurs graphiques existent
            resultDiv.innerHTML = graphs.map(src => 
                `<iframe src="${src}" width="100%" height="500px" style="border:none;"></iframe>` 
            ).join("") + `<div class="stat-interpretation"><p>${interpretationText}</p></div>`;
        }
    </script>
</body>
</html>
