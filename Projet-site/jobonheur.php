<?php
require 'bd.php';

$bdd = getBD();
$stmt = $bdd->query("SELECT clients.nom, reviews.rating, reviews.comment, DATE_FORMAT(reviews.created_at, '%d/%m/%Y') AS date FROM reviews JOIN clients ON reviews.client_id = clients.id_client ORDER BY reviews.created_at DESC");
$reviews = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JOBONHEUR</title>
    <link rel="stylesheet" href="styles/jobonheur.css">
</head>
<body>

    <!-- Landing Section -->
    <div class="landing">
        <h1>JOBONHEUR</h1>
        <p>Match ton travail avec ton style de vie</p>
        <div class="buttons">
            <a href="nouveau.php" class="btn">Créer un compte</a>
            <a href="connexion.php" class="btn">Connexion</a>
        </div>
    </div>

    <!-- Information Section -->
    <div class="info-section">
        <div class="info-box">
            <h3>Qui sommes nous?</h3>
            <p>Nous sommes des étudiantes qui entrons dans le monde du travail sans toujours savoir comment cela fonctionne. Nous avons donc vu le besoin et décidé de créer un espace éducatif avec une plateforme éclairée qui vous permet de mieux comprendre et d'accéder à tout ce qui est nécessaire, au même endroit !</p>
        </div>
        <div class="info-box">
            <h3>Créer son CV :</h3>
            <p>Notre plateforme propose un espace dédié à la création de CV, plus besoin d’autres outils pour le mettre en page.</p>
        </div>
        <div class="info-box">
            <h3>Comparateur</h3>
            <p>Notre innovation repose sur le fait que vous pouvez trouver un type de job selon votre âge et vos habitudes de vie quotidiennes. Que vous cherchiez un emploi stimulant ou plus reposant, c’est chez nous que vous le trouverez !</p>
        </div>
    </div>

    <div class="reviews">
    <h2>Ce que pensent nos utilisateurs :</h2>
    <?php foreach ($reviews as $review): ?>
        <div class="review">
            <p><strong><?= htmlspecialchars($review['nom']) ?></strong> (<?= $review['date'] ?>) :</p>
            <p>⭐ <?= str_repeat('⭐', $review['rating']) ?></p>
            <p><?= htmlspecialchars($review['comment']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<style>
    .reviews { background: #f9f9f9; padding: 20px; border-radius: 10px; margin-top: 20px; }
    .review { border-bottom: 1px solid #ddd; padding: 10px 0; }
    .review p { margin: 5px 0; }
</style>

    <footer>
        <p>© 2024 JOBONHEUR. Tous droits réservés.</p>
    </footer>

</body>
</html>
