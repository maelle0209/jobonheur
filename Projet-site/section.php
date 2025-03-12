<?php
session_start(); //  Important pour récupérer les infos de session

// Vérifier si le client est connecté, sinon rediriger

    if (!isset($_SESSION['client_id'])) {
        header("Location: connexion.php");
        exit;
    }
    $client_id = $_SESSION['client_id']; // ✅ Récupérer l'ID pour l'utiliser
    
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section</title>
    <link rel="stylesheet" href="styles/section.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

</head>
<body>

    <!-- Déconnexion -->
    <div class="header">
        <a href="jobonheur.php" class="btn-retour">Déconnexion</a>
    </div>

    <!-- Message de connexion réussi -->
    <h2 class="welcome-message">Félicitations, vous êtes maintenant connecté !</h2>

    
    <!-- Section des options -->
    <div class="option-section">
        <a href="cv.php" class="btn-option">CV</a>
        <a href="match.php" class="btn-option">Match</a>
        <a href="modevie.html" class="btn-option">Découvre ton métier</a>
        <a href="statistiques.php" class="btn-option">Statistique</a>
    </div>
<!-- Icône pour ajouter un avis -->
<div class="review-icon" onclick="openReviewModal()">
    <i class="fas fa-star"></i> <!-- Icône étoile FontAwesome -->
</div>

<!-- Fenêtre modale pour soumettre un avis -->
<div id="reviewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeReviewModal()">&times;</span>
        <h2>Laisser un avis</h2>
        <form action="save_review.php" method="POST">
            <label for="rating">Note :</label>
            <select name="rating" required>
                <option value="5">⭐️⭐️⭐️⭐️⭐️ (Excellent)</option>
                <option value="4">⭐️⭐️⭐️⭐️ (Bien)</option>
                <option value="3">⭐️⭐️⭐️ (Moyen)</option>
                <option value="2">⭐️⭐️ (Mauvais)</option>
                <option value="1">⭐️ (Très mauvais)</option>
            </select>
            <label for="comment">Commentaire :</label>
            <textarea name="comment" required></textarea>
            <button type="submit">Envoyer</button>
        </form>
    </div>
</div>

<style>
    .review-icon {
        position: fixed;
        bottom: 20px;
        right: 20px;
    }
    .modal { display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px gray; }
    .modal-content { text-align: center; }
    .close { position: absolute; top: 10px; right: 10px; cursor: pointer; }
</style>

<script>
    function openReviewModal() { document.getElementById("reviewModal").style.display = "block"; }
    function closeReviewModal() { document.getElementById("reviewModal").style.display = "none"; }
</script>


</body>
</html>
