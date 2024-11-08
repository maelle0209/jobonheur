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
        header('Location: jobonheur.php');
        exit;
    } else {
        echo '<p style="color: red;">Erreur : e-mail ou mot de passe incorrect. <a href="connexion.php">Réessayer</a>.</p>';
    }
} else {
    echo '<p style="color: red;">Erreur : veuillez fournir votre e-mail et votre mot de passe. <a href="connexion.php">Réessayer</a>.</p>';
}
?>

