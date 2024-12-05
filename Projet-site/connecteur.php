<?php
session_start();
include 'bd.php';

if (isset($_POST['mail']) && isset($_POST['mdp'])) {
    $email = $_POST['mail'];
    $motDePasse = $_POST['mdp'];

    $bdd = getBD();

    $stmt = $bdd->prepare('SELECT * FROM clients WHERE mail = :mail');
    $stmt->execute([':mail' => $email]);

    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($client && password_verify($motDePasse, $client['mdp'])) {
        $_SESSION['client'] = [
            'id' => $client['id_client'],
            'nom' => $client['nom'],
            'prenom' => $client['prenom'],
            'email' => $client['mail']
        ];
        header('Location: section.php');
        exit;
    } else {
        $error = "Erreur : e-mail ou mot de passe incorrect.";
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $error = "Erreur : veuillez fournir votre e-mail et votre mot de passe.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Jobonheur</title>
    <style>
        /* Style de fond */
        html {
            background-color: #fdf3e3; /* Couleur crème */
            font-family: Arial, sans-serif; /* Police par défaut */
        }

        /* Style général des titres */
        h1 {
            text-align: center;
            color: #000; /* Noir */
            font-size: 2.5em; /* Taille agrandie */
            margin-top: 20px;
            font-weight: bold;
        }

        /* Texte centré */
        p {
            text-align: center;
            color: black;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        /* Conteneur */
        .container {
            background-color: #fff; /* Fond blanc */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombre légère */
            width: 400px;
            margin: 20px auto; /* Centrer */
            padding: 20px;
            text-align: center;
        }

        /* Message d'erreur */
        .error {
            color: #e94d20; /* Orange */
            margin-bottom: 20px;
        }

        /* Image d'erreur */
        .error img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        /* Boutons */
        .button {
            display: block;
            background-color: #e94d20; /* Orange */
            color: white;
            font-size: 1.1em;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            margin-top: 10px;
        }

        .button:hover {
            background-color: #c13b18; /* Orange foncé */
        }

        /* Liens */
        a {
            color: #e94d20; /* Couleur des liens */
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Oups...</h1>

        <?php if (isset($error)): ?>
            <div class="error">
                <img src="images/erreur11.png" alt="Erreur">
                <p><?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>

        <!-- Lien pour revenir à la page de connexion -->
        <a href="connexion.php" class="button">Retourner à la connexion</a>
    </div>
</body>
</html>
