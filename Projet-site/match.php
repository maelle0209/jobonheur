<?php
session_start();
include 'bd.php';

if (!isset($_SESSION['client_id'])) {
    header("Location: connexion.php");
    exit;
}

$userId = $_SESSION['client_id'];
$bdd = getBD();

$queryCV = "SELECT skills, education_level, experience FROM cv WHERE id_client = ?";
$stmtCV = $bdd->prepare($queryCV);
$stmtCV->execute([$userId]);
$cv = $stmtCV->fetch();

if (!$cv) {
    echo "<p>Aucun CV trouvé. Veuillez compléter votre profil.</p>";
    exit;
}

$queryJobs = "SELECT `job_id`, `Intitulé du poste`, `Description`, `Ville`, `Type de contrat`, `salaire`, `Entreprise`, `lien`, `niveau libellé`,`compétences exigées`, `Qualité professionnelles`,  `Experience exigé` FROM response_2";
$stmtJobs = $bdd->query($queryJobs);
$jobs = $stmtJobs->fetchAll();

$recommendations = [];

function normalizeSkills($skills) {
    return array_map('trim', array_map('strtolower', explode(',', $skills)));
}

foreach ($jobs as $job) {
    $score = 0;
    $jobSkills = normalizeSkills($job['compétences exigées']);
    $cvSkills = normalizeSkills($cv['skills']);
    
    $matchingSkills = count(array_intersect($jobSkills, $cvSkills));
    $score += ($matchingSkills / max(1, count($jobSkills))) * 60;
    
    if (strtolower($job['niveau libellé']) == strtolower($cv['education_level'])) {
        $score += 20;
    }
    
    $jobExperience = (int) $job['Experience exigé'];
    $cvExperience = (int) $cv['experience'];
    
    if ($cvExperience >= $jobExperience) {
        $score += 20;
    } elseif ($cvExperience >= ($jobExperience * 0.75)) {
        $score += 10;
    }
    
    $recommendations[] = ['job' => $job, 'score' => $score];
}

usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);
##########################################################################################################"

$query = "SELECT `job_id`, `Intitulé du poste`, `Description`, `Ville`, `Type de contrat`, `salaire`, `Entreprise`, `lien`, `niveau libellé`,`compétences exigées`, `Qualité professionnelles`
          FROM response_2 WHERE `job_id` = ?";
$stmt = $bdd->prepare($query);
$stmt->execute([$jobId]);
$job = $stmt->fetch();

/// Récupérer l'ID du job suivant (l'ID du job actuel + 1 ou par une autre méthode)
$nextJobId = $jobId + 1; // Exemple pour passer au job suivant par ID
$queryNextJob = "SELECT job_id FROM response_2 WHERE job_id = ?";
$stmtNextJob = $bdd->prepare($queryNextJob);
$stmtNextJob->execute([$nextJobId]);
$nextJob = $stmtNextJob->fetch();

if (!$nextJob) {
    // Si l'ID suivant n'existe pas, prenez l'ID du premier job
    $nextJobId = 1;
}
################################################################################################################
// Maintenant, vous pouvez utiliser $nextJobId pour la redirection après avoir refusé l'offre
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'favoris') {
        // Ajouter aux favoris
        $favQuery = "INSERT INTO favoris (user_id, job_id) VALUES (?, ?)";
        $stmt = $bdd->prepare($favQuery);
        $stmt->execute([$userId, $jobId]);

        // Mise à jour du dernier job vu
        $queryUpdate = "UPDATE clients SET last_job_id = ? WHERE id_client = ?";
        $stmtUpdate = $bdd->prepare($queryUpdate);
        $stmtUpdate->execute([$nextJobId, $userId]);

        // Rediriger vers le prochain job
        header("Location: match.php?job_id=$nextJobId");
        exit; // Assurez-vous que la redirection s'arrête ici
    } elseif ($_POST['action'] === 'refus') {
        $queryUpdate = "UPDATE clients SET last_job_id = ? WHERE id_client = ?";
        $stmtUpdate = $bdd->prepare($queryUpdate);
        $stmtUpdate->execute([$nextJobId, $userId]);
        // Passer à l'offre suivante
        header("Location: match.php?job_id=$nextJobId");
        exit; // Assurez-vous que la redirection s'arrête ici
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres Recommandées</title>
    <link rel="stylesheet" href="styles/match.css">
</head>
<body>
    <div class="container">
        <!-- Section des favoris -->
        <div class="favorites">
            <h2>Vos Favoris</h2>
            <ul>
                <?php
                // Récupérer les favoris pour l'utilisateur connecté
                $favQuery = "SELECT favoris.job_id, `Intitulé du poste`, `Entreprise` 
             FROM favoris 
             JOIN response_2 ON favoris.job_id = response_2.job_id
             WHERE favoris.user_id = ?";
             $stmt = $bdd->prepare($favQuery);
             $stmt->execute([$userId]);
             $favoris = $stmt->fetchAll();


                if ($favoris) {
                    foreach ($favoris as $favori) {
                        echo "<li onclick=\"window.location.href='fiche_poste.php?job_id={$favori['job_id']}'\">";
                        echo htmlspecialchars($favori['Intitulé du poste']) . " - " . htmlspecialchars($favori['Entreprise']);
                        echo "</li>";
                    }
                } else {
                    echo "<li>Aucun favori pour l'instant.</li>";
                }
                ?>
            </ul>
        </div>
        
        <div class="job-list">
            <?php foreach (array_slice($recommendations, 0, 5) as $rec) : ?>
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
                    <p><strong>Score :</strong> <?= number_format($rec['score'], 2) ?>%</p>
                </div>
            <?php endforeach; ?>
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
        </div>
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
