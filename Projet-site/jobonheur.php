<?php
require 'bd.php';

$bdd = getBD();
$stmt = $bdd->query("SELECT clients.nom, reviews.rating, reviews.comment, DATE_FORMAT(reviews.created_at, '%d/%m/%Y') AS date FROM reviews JOIN clients ON reviews.client_id = clients.id_client ORDER BY reviews.created_at DESC");
$reviews = $stmt->fetchAll();
?>
<?php
session_start(); //  Important pour récupérer les infos de session

$client_id = $_SESSION['client_id'] ?? null; // Si la session n'existe pas, $client_id vaut null

?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JOBONHEUR</title>
  <link rel="stylesheet" href="styles/jobonheur.css">
  <!-- Inclusion de Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
        integrity="sha512-p1Cm9EC5iXxclI0uAwO4UE5r5p4hp3WNT03TlVZK/6k+22y6i7gR0gN1BkXqN4fCD/Ji3dOv2+HC0/7clR3gdg==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

<div class="landing">
    <h1>JOBONHEUR</h1>
    <p>Match ton travail avec ton style de vie</p>
    <div class="buttons">
      <?php if ($client_id): ?>
        <a href="section.php" class="btn">Accéder aux fonctionnalités</a>
        <a href="deconnexion.php" class="btn">Déconnexion</a>
      <?php else: ?>
        <a href="nouveau.php" class="btn">Créer un compte</a>
        <a href="connexion.php" class="btn">Connexion</a>
      <?php endif; ?>
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
  <!-- Icône pour ouvrir le chatbot -->
  <div id="chatbot-toggle">
    <i class="fas fa-robot"></i>
    <span id="chat-bubble"><i class="fas fa-comment"></i></span>
  </div>

  <!-- Container du chatbot -->
<div id="chat-container">
  <div id="chat-box">
    <!-- Section FAQ à afficher en haut lors de l'ouverture du chat -->
    <div id="faq">
      <h3>Questions fréquentes</h3>
      <ul>
        <li onclick="selectFAQ('Comment rédiger un bon CV ?')">Comment rédiger un bon CV ?</li>
        <li onclick="selectFAQ('Quels conseils pour un entretien ?')">Quels conseils pour un entretien ?</li>
        <li onclick="selectFAQ('Comment négocier son salaire ?')">Comment négocier son salaire ?</li>
        <!-- Ajoutez d'autres questions fréquentes ici -->
      </ul>
    </div>
    <!-- Zone de conversation -->
    <div id="chat-content">
      <p><strong>JobBot:</strong> Bonjour ! Comment puis-je vous aider ?</p>
    </div>
  </div>
  <input type="text" id="chat-input" placeholder="Écrivez un message...">
  <button onclick="sendMessage()">Envoyer</button>
</div>


  <!-- JavaScript pour gérer l'affichage et l'envoi des messages -->
  <script>
  // Fonction appelée lorsqu'une FAQ est cliquée
  function selectFAQ(question) {
    // Masquer la section FAQ une fois une question sélectionnée (optionnel)
    document.getElementById("faq").style.display = 'none';
    // Remplir le champ de saisie avec la question
    document.getElementById("chat-input").value = question;
    // Envoyer automatiquement la question
    sendMessage();
  }

  // Bascule l'affichage du container du chatbot lors du clic sur l'icône
  document.getElementById("chatbot-toggle").addEventListener("click", function(){
    var chatContainer = document.getElementById("chat-container");
    if (chatContainer.style.display === "none" || chatContainer.style.display === "") {
      chatContainer.style.display = "block";
      // Réafficher la FAQ si elle était masquée (par exemple à l'ouverture du chat)
      document.getElementById("faq").style.display = 'block';
    } else {
      chatContainer.style.display = "none";
    }
  });

  // Fonction pour envoyer un message au chatbot
  function sendMessage() {
    let userInput = document.getElementById("chat-input").value;
    let chatContent = document.getElementById("chat-content");

    if (userInput.trim() === "") return;

    // Affiche le message de l'utilisateur
    chatContent.innerHTML += `<p><strong>Vous:</strong> ${userInput}</p>`;

    // Envoie le message à l'API Flask
    fetch("http://127.0.0.1:5000/chat", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ message: userInput })
    })
    .then(response => response.json())
    .then(data => {
      chatContent.innerHTML += `<p><strong>ChatBot:</strong> ${data.response}</p>`;
      chatContent.scrollTop = chatContent.scrollHeight; // Fait défiler vers le bas
    })
    .catch(error => {
      console.error("Erreur de communication avec l'API:", error);
      chatContent.innerHTML += `<p><strong>ChatBot:</strong> Erreur de connexion au serveur.</p>`;
    });

    // Efface le champ de saisie
    document.getElementById("chat-input").value = "";
  }
</script>


  <!-- Reviews Section -->
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

  <!-- Footer Section -->
  <footer>
    <p>© 2024 JOBONHEUR. Tous droits réservés.</p>
  </footer>

</body>
</html>
