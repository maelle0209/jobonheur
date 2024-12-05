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
    <div class="action-indicator"></div> <!-- Indicateur d'animation -->
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
        <!-- Boutons modernisés -->
        <form method="POST">
            <button type="submit" name="action" value="refus" class="action-btn cross">❌</button>
            <button type="submit" name="action" value="favoris" class="action-btn heart">❤️</button>
        </form>
    </div>
</div>

    
    <div class="header">
        <a href="section.php" class="btn-retour">Retour à l'accueil</a>
    </div>

    <script>
    const jobCard = document.querySelector('.job-card');
    const actionIndicator = jobCard.querySelector('.action-indicator');
    let startX = 0;
    let currentX = 0;
    let isDragging = false;

    function startSwipe(e) {
        isDragging = true;
        startX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
        jobCard.style.transition = 'none'; // Désactive la transition
    }

    function swipe(e) {
        if (!isDragging) return;
        currentX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
        const translateX = currentX - startX;

        jobCard.style.transform = `translateX(${translateX}px) rotate(${translateX / 20}deg)`;

        // Indicateur d'action (favoris ou refus)
        if (translateX > 0) {
            actionIndicator.textContent = 'Favoris';
            actionIndicator.style.color = '#4caf50'; // Vert
            actionIndicator.style.opacity = 1;
        } else if (translateX < 0) {
            actionIndicator.textContent = 'Rejeté';
            actionIndicator.style.color = '#f44336'; // Rouge
            actionIndicator.style.opacity = 1;
        }
    }

    function endSwipe() {
        if (!isDragging) return;
        isDragging = false;

        const translateX = currentX - startX;
        const screenWidth = window.innerWidth;

        if (translateX > screenWidth / 4) {
            // Swipe à droite (favoris)
            jobCard.classList.add('swipe-right');
            setTimeout(() => performAction('favoris'), 500);
        } else if (translateX < -screenWidth / 4) {
            // Swipe à gauche (refus)
            jobCard.classList.add('swipe-left');
            setTimeout(() => performAction('refus'), 500);
        } else {
            // Retour au centre
            jobCard.style.transition = 'transform 0.3s ease';
            jobCard.style.transform = 'translateX(0) rotate(0)';
            actionIndicator.style.opacity = 0; // Cache l'indicateur
        }
    }

    function performAction(action) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = action;
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
    }

    // Écouteurs pour la souris et le tactile
    jobCard.addEventListener('mousedown', startSwipe);
    jobCard.addEventListener('mousemove', swipe);
    jobCard.addEventListener('mouseup', endSwipe);
    jobCard.addEventListener('mouseleave', endSwipe);

    jobCard.addEventListener('touchstart', startSwipe);
    jobCard.addEventListener('touchmove', swipe);
    jobCard.addEventListener('touchend', endSwipe);
</script>


   
</body>
</html>
