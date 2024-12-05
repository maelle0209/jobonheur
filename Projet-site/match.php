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
$query = "SELECT `job_id`, `Intitulé du poste`, `Description`, `Ville`, `Type de contrat`, `salaire`, `Entreprise`, `lien`, `niveau libellé`,`compétences exigées`, `Qualité professionnelles`
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
    <link rel="stylesheet" href="styles/match.css">

</head>
<body>
    <div class="job-card">
        <h2><?= htmlspecialchars($job['Intitulé du poste']) ?></h2>
        <p><strong>Entreprise :</strong> <?= htmlspecialchars($job['Entreprise']) ?: 'Non spécifiée' ?></p>
        <p><strong>Ville :</strong> <?= htmlspecialchars($job['Ville']) ?></p>
        <p><strong>Type de contrat :</strong> <?= htmlspecialchars($job['Type de contrat']) ?></p>
        <p><strong>Salaire :</strong> <?= htmlspecialchars($job['salaire']) ?: 'Non précisé' ?></p>
        <p><strong>Niveau exigé :</strong> <?= htmlspecialchars($job['niveau libellé']) ?: 'Non précisé' ?></p>
        <p><strong>Compétences exigées :</strong> <?= htmlspecialchars($job['compétences exigées']) ?: 'Non précisé' ?></p>
        <p><strong>Qualitées professionnelles :</strong> <?= htmlspecialchars($job['Qualité professionnelles']) ?: 'Non précisé' ?></p>
        <p><?= nl2br(htmlspecialchars($job['Description'])) ?></p> 

        <div class="actions">
            <!-- Formulaire pour gérer les actions (favoris ou refus) -->
            <form method="POST">
                <button type="submit" name="action" value="refus" class="action-btn cross">❌</button>
                <button type="submit" name="action" value="favoris" class="action-btn heart">❤️</button>
            </form>
        </div>
    </div>
    <div class="header">
        <a href="section.php" class="btn-retour">Retour à l'accueil</a>
    </div>
   
</body>
</html>
