<?php

include 'bd.php';


function enregistrer($nom, $prenom, $email, $motDePasse, $sexe) {
    
     $bdd = getBD();
    
     $sql = "INSERT INTO clients (nom, prenom, mail, mdp, sexe) VALUES (?, ?, ?, ?, ?)";
     $stmt = $bdd->prepare($sql);
    
    // Exécuter la requête avec les paramètres
     $stmt->execute([$nom, $prenom, $email, $motDePasse, $sexe]);
}

// Récupérer les données du formulaire
$nom = isset($_GET['n']) ? $_GET['n'] : '';
$prenom = isset($_GET['p']) ? $_GET['p'] : '';
$email = isset($_GET['mail']) ? $_GET['mail'] : '';
$mdp1 = isset($_GET['mdp1']) ? $_GET['mdp1'] : '';
$mdp2 = isset($_GET['mdp2']) ? $_GET['mdp2'] : '';
$sexe = isset($_GET['sexe']) ? $_GET['sexe'] : ''; // Récupération du sexe

// Vérification des données
if (empty($nom) || empty($prenom) || empty($email) || $mdp1 !== $mdp2) {
    // Si un champ est vide ou les mots de passe ne correspondent pas, rediriger vers "nouveau.php" avec les valeurs déjà saisies
    $url = "nouveau.php?n=" . urlencode($nom) . "&p=" . urlencode($prenom) . "&mail=" . urlencode($email);
    
    // Redirection vers le formulaire avec les valeurs déjà saisies
    echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
    exit;
} else {
    // Si tout est correct, hacher le mot de passe avant de l'enregistrer
    $motDePasseHashe = password_hash($mdp1, PASSWORD_DEFAULT);
    
    // Enregistrer les données dans la base de données
    enregistrer($nom, $prenom, $email, $motDePasseHashe, $sexe);
    
    // Redirection vers la page d'accueil ou de confirmation
      echo '<meta http-equiv="refresh" content="0;url=jobonheur.php">';
}
?>
