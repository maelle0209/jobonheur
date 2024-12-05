<?php
session_start();
include 'bd.php'; // Inclure la fonction pour se connecter à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['client'])) {
    header("Location: connexion.php"); // Rediriger vers la page de connexion si non connecté
    exit;
}

$userId = $_SESSION['client']['id']; // Récupérer l'ID de l'utilisateur connecté

// Connexion à la base de données
$bdd = getBD(); // Fonction pour se connecter à la base de données

// Récupérer l'ID de l'offre de la requête, sinon prendre la première offre
$jobId = isset($_GET['job_id']) ? $_GET['job_id'] : 1;

// Récupérer l'offre actuelle
$query = "SELECT `job_id`, `Intitulé du poste`, `Description`, `Ville`, `Type de contrat`, `salaire`, `Entreprise`, `lien` 
          FROM response_2 WHERE `job_id` = ?";
$stmt = $bdd->prepare($query);
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    // Si aucun résultat, afficher un message d'erreur ou rediriger
    echo 'Aucune offre trouvée.';
    exit;
}
// Enregistrer le favori ou refuser l'offre
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'favoris') {
        // Ajouter aux favoris
        $favQuery = "INSERT INTO favoris (user_id, job_id) VALUES (?, ?)";
        $stmt = $bdd->prepare($favQuery);
        $stmt->execute([$userId, $jobId]);
        $nextJobId = $jobId + 1; // Exemple de logique pour passer à l'offre suivante
        header("Location: match.php?job_id=$nextJobId");
    } elseif ($_POST['action'] === 'refus') {
        // Passer à l'offre suivante
        $nextJobId = $jobId + 1; // Exemple de logique pour passer à l'offre suivante
        header("Location: match.php?job_id=$nextJobId"); // Rediriger vers la prochaine offre
        exit;
    }
    echo "<script>
    document.body.classList.add('$action');
    setTimeout(function() {
        window.location = 'match.php?job_id=$nextJobId';
    }, 1000); // Attend que l'animation soit terminée avant la redirection
</script>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offre d'Emploi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
             /* Empêche la barre de défilement pendant l'animation */
            transition: transform 1s ease;
        }
        /* Animation pour les différentes actions */
        body.favoris {
            transform: translateX(100%); /* Déplace la page vers la droite */
        }

        body.refus {
            transform: translateX(-100%); /* Déplace la page vers la gauche */
        }

        .job-card {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        .actions {
            margin-top: 20px;
        }

        .action-btn {
            background-color: #f0f0f0;
            border: none;
            padding: 10px;
            border-radius: 50%;
            margin: 0 20px;
            cursor: pointer;
        }

        .action-btn:hover {
            background-color: #ddd;
        }
        .heart {
            color: red;
        }
        .cross {
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="job-card">
        <h2><?= htmlspecialchars($job['Intitulé du poste']) ?></h2>
        <p><strong>Entreprise :</strong> <?= htmlspecialchars($job['Entreprise']) ?: 'Non spécifiée' ?></p>
        <p><strong>Ville :</strong> <?= htmlspecialchars($job['Ville']) ?></p>
        <p><strong>Type de contrat :</strong> <?= htmlspecialchars($job['Type de contrat']) ?></p>
        <p><strong>Salaire :</strong> <?= htmlspecialchars($job['salaire']) ?: 'Non précisé' ?></p>
        <p><?= nl2br(htmlspecialchars($job['Description'])) ?></p>

        <div class="actions">
            <!-- Formulaire pour gérer les actions (favoris ou refus) -->
            <form method="POST">
                <button type="submit" name="action" value="refus" class="action-btn cross">❌</button>
                <button type="submit" name="action" value="favoris" class="action-btn heart">❤️</button>
            </form>
        </div>
    </div>
</body>
</html>
