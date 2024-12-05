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

// Récupérer l'ID du job depuis la requête GET
$jobId = isset($_GET['job_id']) ? $_GET['job_id'] : 1; // Valeur par défaut si non spécifiée

// Récupérer l'offre actuelle
$query = "SELECT `job_id`, `Intitulé du poste`, `Description`, `Ville`, `Type de contrat`, `salaire`, `Entreprise`, `lien`, `niveau libellé`, `compétences exigées`, `Qualité professionnelles`
          FROM response_2 WHERE `job_id` = ?";
$stmt = $bdd->prepare($query);
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    // Si aucun résultat, afficher un message d'erreur ou rediriger
    echo 'Aucune offre trouvée.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'offre</title>
    <link rel="stylesheet" href="styles/match.css">
</head>
<body>
    <div class="container">
        <div class="job-container">
            <div class="job-card">
                <h2><?= htmlspecialchars($job['Intitulé du poste']) ?></h2>
                <p><strong>Entreprise :</strong> <?= htmlspecialchars($job['Entreprise']) ?: 'Non spécifiée' ?></p>
                <p><strong>Ville :</strong> <?= htmlspecialchars($job['Ville']) ?></p>
                <p><strong>Type de contrat :</strong> <?= htmlspecialchars($job['Type de contrat']) ?></p>
                <p><strong>Salaire :</strong> <?= htmlspecialchars($job['salaire']) ?: 'Non précisé' ?></p>
                <p><strong>Niveau exigé :</strong> <?= htmlspecialchars($job['niveau libellé']) ?: 'Non précisé' ?></p>
                <p><strong>Compétences exigées :</strong> <?= htmlspecialchars($job['compétences exigées']) ?: 'Non précisé' ?></p>
                <p><strong>Qualités professionnelles :</strong> <?= htmlspecialchars($job['Qualité professionnelles']) ?: 'Non précisé' ?></p>
                <p><?= nl2br(htmlspecialchars($job['Description'])) ?></p> 
                <?php if (!empty($job['lien'])): ?>
                    <p><strong><a href="<?= htmlspecialchars($job['lien']) ?>" target="_blank" class="apply-link">Postuler ici</a></strong></p>
                <?php else: ?>
                    <p><strong>Aucun lien de candidature disponible.</strong></p>
                <?php endif; ?>
            </div>

            <div class="actions">
                <!-- Bouton retour vers les matchs -->
                <a href="match.php" class="btn-retour">Retour aux Matchs</a>
            </div>
        </div>
    </div>
</body>
</html>
