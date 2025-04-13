<!DOCTYPE html>
<html lang="fr">
<head>
<meta http-equiv="Content-Type"
content="text/html; charset=UTF-8" />
<link rel="stylesheet" href=
"styles/nouveau.css" type="text/css"
media="screen" />
<title>Nouveau</title>
</head>
<body>
<!-- Formulaire de nouveau utilisateur -->
    <h1>Nouveau Client</h1> 
    <p>Création de Compte - Nouveau Client</p>
    <form method="get" action="enregistrement.php" autocomplete="off">
     <p>
        <label for="name">Nom :</label>
        <input type="text" id="name" name="n" value="<?php echo isset($_GET['n']) ? htmlspecialchars($_GET['n']) : ''; ?>">
     </p>
     <p>
        <label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="p" value="<?php echo isset($_GET['p']) ? htmlspecialchars($_GET['p']) : ''; ?>">
     </p>
     <p>
        <label for="email">Adresse e-mail :</label>
        <input type="email" id="email" name="mail" value="<?php echo isset($_GET['mail']) ? htmlspecialchars($_GET['mail']) : ''; ?>">
     </p>
     <p>
        <label for="password1">Mot de passe :</label>
        <input type="password" id="password1" name="mdp1">
     </p>
     <p>
        <label for="password2">Confirmer votre mot de passe :</label>
        <input type="password" id="password2" name="mdp2">
     </p>
     <p>
         <label>Sexe :</label>
         <input type="radio" id="homme" name="sexe" value="Homme" required>
         <label for="homme">Homme</label>
         <input type="radio" id="femme" name="sexe" value="Femme">
         <label for="femme">Femme</label>
      </p>

     <p>
        <input type="submit" value="Envoyer">
     </p>
</form>

</body>
</html>