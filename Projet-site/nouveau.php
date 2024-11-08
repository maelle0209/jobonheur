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

    <h1>Nouveau Client</h1>
    <p>Création de Compte - Nouveau Client</p>
    <form method="get" action="enregistrement.php" autocomplet="off">
 
    <p>
            Nom :
            <input type="text" name="n" value="<?php echo isset($_GET['n']) ? htmlspecialchars($_GET['n']) : ''; ?>"/>
        </p>
        <p>
            Prénom :
            <input type="text" name="p" value="<?php echo isset($_GET['p']) ? htmlspecialchars($_GET['p']) : ''; ?>"/>
        </p>
       
        <p>
            Adresse e-mail :
            <input type="email" name="mail" value="<?php echo isset($_GET['mail']) ? htmlspecialchars($_GET['mail']) : ''; ?>"/>
        </p>
        <p>
            Mot de passe :
            <input type="password" name="mdp1" value=""/>
        </p>
        <p>
            Confirmer votre mot de passe :
            <input type="password" name="mdp2" value=""/>
        </p>
        <p>
            <input type="submit" value="Envoyer">
        </p>
    </form>
</body>
</html>