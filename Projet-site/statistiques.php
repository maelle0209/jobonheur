<?php
session_start();
include 'bd.php';  // Connexion à la base de données
$bdd = getBD();  

// Vérifier si l'ID client est dans les données envoyées
if (isset($data['id_client']) && !empty($data['id_client'])) {
    $id_client = $data['id_client'];
} elseif (isset($_SESSION['client_id'])) {
    // Si l'ID client n'est pas dans la requête POST, vérifier dans la session
    $id_client = $_SESSION['client_id'];
} else {
    // Si l'ID client n'est pas fourni ni en POST ni en session
    echo json_encode(['error' => 'ID client non fourni']);
    exit();
}

// Récupérer les données du client (sexe, âge, niveau d'éducation)
$query = "SELECT clients.sexe, cv.age_category, cv.education_level
          FROM clients
          JOIN cv ON clients.id_client = cv.id_client
          WHERE clients.id_client = :id_client LIMIT 1";

$stmt = $bdd->prepare($query);
$stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
$stmt->execute();

// Vérifier si des données sont retournées
if ($stmt->rowCount() > 0) {
    $clientData = $stmt->fetch(PDO::FETCH_ASSOC);  // Récupérer les données
} else {
    echo json_encode(['error' => 'Client non trouvé']);
    exit();
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
    <!-- Stocker l'ID client dans un élément caché -->
    <div id="client_id" style="display:none;"><?php echo $id_client; ?></div>

    <div class="header">
        <a href="section.php" class="btn-retour">Retour à l'accueil</a>
    </div>

    <div class="presentation">
        <h2>Les statistiques du chômage en France selon l'INSEE</h2>
        <p>Les données suivantes reflètent l'évolution du chômage en France.</p>
    </div>
    <!--Affichage des données de l'utilisateur -->
    <div class="client-info">
        <p>Cliquez sur <strong>Mon risque de chômage</strong> pour connaitre votre taux de chômage moyen par rapport à vos données:</p>
        <div><strong>Sexe:</strong> <span id="sexe"><?= $clientData['sexe'] ?></span></div>
        <div><strong>Catégorie d'âge:</strong> <span id="age_category"><?= $clientData['age_category'] ?></span></div>
        <div><strong>Niveau de diplôme:</strong> <span id="education_level"><?= $clientData['education_level'] ?></span></div>
    </div>
    <!--tous les boutons présents sur la page -->
    <div class="option-section">
        <button class="btn-option" onclick="showStat('sexe')">Statistiques par Sexe</button>
        <button class="btn-option" onclick="showStat('age')">Statistiques par Âge</button>
        <button class="btn-option" onclick="showStat('diplome')">Statistiques par Diplôme</button>
        <button class="btn-option" onclick="predictChomage()">Mon risque de chômage</button>
    </div>

    <!-- Résultats de la prédiction et des conseils -->
    <div id="prediction-result"></div> 
    <div id="advice"></div> 

     <!-- Section pour afficher les figures et les interprétations -->
    <div id="stat-result" class="stat-result">
        <!-- Indicateur de chargement placé à l'intérieur de la section -->
        <div id="loading" style="display: none;">
            <p>Les graphiques sont en train de se charger...</p>
            <!-- Vous pouvez ajouter un spinner ici -->
        </div>
        <img src="" alt="statistiques" id="stat-image" style="display: none;" />
        <div class="stat-interpretation" id="stat-interpretation">
            <p>Sélectionnez une option pour afficher les statistiques associées et leur interprétation.</p>
        </div>
    </div>


    <script>
        // Fonction pour afficher les statistiques avec des graphiques HTML
        function showStat(option) {
            const resultDiv = document.getElementById('stat-result');
            let graphs = [];
            let interpretationText = "";
            // affichage du graphique selon l'option choisi et le bouton sur lequel on clique
            if (option === "sexe") {
                graphs = [
                    "graphs/graphique_sexe.html",
                    "graphs/graphique_sexe2017.html",
                    "graphs/graphique_sexe2000.html",
                    "graphs/graphique_sexe1995.html",
                    "graphs/graphique_sexe1975.html"
                ];
                interpretationText = "Cette statistique montre les taux de chômage en fonction du sexe.";
            } else if (option === "age") {
                graphs = ["graphs/graphique_age.html"];
                interpretationText = "Cette statistique illustre les taux de chômage selon les tranches d’âge.";
            } else if (option === "diplome") {
                graphs = ["graphs/graphique_diplome.html"];
                interpretationText = "Cette statistique analyse l’impact du niveau de diplôme sur le chômage.";
            }

            //En se basant sur Chatgpt: j'ai utilisé graphs.map pour regrouper et générer plusieurs iframes si plusieurs graphiques existent pour regrouper tous les graphiques sexe puisqu on les a pour plusieurs années
            resultDiv.innerHTML = graphs.map(src => 
                `<iframe src="${src}" width="100%" height="500px" style="border:none;"></iframe>` 
            ).join("") + `<div class="stat-interpretation"><p>${interpretationText}</p></div>`;
             // Masquer l'indicateur de chargement et afficher les statistiques
             setTimeout(() => {
                const loadingDiv = document.getElementById('loading');
                const statContentDiv = document.getElementById('stat-result');
                
                if (loadingDiv && statContentDiv) {
                    loadingDiv.style.display = 'none';  // Masquer l'indicateur de chargement
                    statContentDiv.style.display = 'block';  // Afficher les statistiques
                }
            }, 1000);  // Ajustez ce délai en fonction du temps réel de chargement
        }

        // Fonction pour tester la prédiction du chômage
        function predictChomage() {
            // Récupérer l'ID client depuis l'élément caché
            const idClient = document.getElementById('client_id').textContent;
            const sexe = document.getElementById('sexe').textContent;
            const ageCategory = document.getElementById('age_category').textContent;
            const educationLevel = document.getElementById('education_level').textContent;

            if (!idClient || !sexe || !ageCategory || !educationLevel) {
                document.getElementById('error-message').textContent = 'Certaines informations nécessaires pour la prédiction sont manquantes. Veuillez compléter votre profil CV.';
                return;
            }

            const requestData = {
                id_client: idClient,  // Envoi de l'ID client récupéré
                sexe: sexe,
                age_category: ageCategory,
                education_level: educationLevel
            };

            console.log('Données envoyées:', requestData); // Afficher les données envoyées dans la console
            // lancer la prédiction avec le chemin Flask en 
            fetch('http://127.0.0.1:5000/predict', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Réponse du serveur:', data); // Afficher la réponse du serveur
                if (data.prediction) {                    
                    // Applique le style à prediction-result
                    const predictionResult = document.getElementById('prediction-result');
                    if (predictionResult) {
                        predictionResult.innerHTML = `Prédiction du taux de chômage : ${data.prediction}`;
                        predictionResult.classList.add('stat-interpretation'); // Ajoute la classe
                    }
                    
                    // Applique le style à advice (conseils)
                    const advice = document.getElementById('advice');
                    if (advice && data.advice) {
                        // Formater les conseils en gras et sous forme de liste
                        const adviceList = data.advice.map(adviceItem => {
                            return `<li><strong>${adviceItem}</strong></li>`;  // Ajouter des balises <strong> et <li>
                        }).join('');
                        advice.innerHTML = `<ul>${adviceList}</ul>`; // Créer une liste <ul> avec les conseils formatés
                        advice.classList.add('stat-interpretation'); // Ajoute la classe
                    }
                } else {
                    alert("Erreur de prédiction");
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

    </script>
</body>
</html>
