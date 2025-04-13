<?php
session_start();
echo "Client connecté : " . $_SESSION['client_id'];
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles/nouveau.css" type="text/css" media="screen" />
    <title>Connexion</title>
</head>
<body>
<!-- Formulaire de connexion -->
<h1>Connexion</h1>
<p>Pas encore de compte ? <a href="nouveau.php">Créer un compte</a></p>

<form method="post" action="connecteur.php">
    <p>
        Adresse e-mail :
        <input type="email" name="mail" required>
    </p>
    <p>
        Mot de passe :
        <input type="password" name="mdp" required>
    </p>
    <p>
        <input type="submit" value="Se connecter">
    </p>
</form>

</body>
</html>



