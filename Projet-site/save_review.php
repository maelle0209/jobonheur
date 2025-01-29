<?php
session_start();
require 'bd.php';

if (!isset($_SESSION['client_id'])) {
    die("❌ Erreur : Vous devez être connecté pour laisser un avis.");
}

$client_id = $_SESSION['client_id'];
$rating = $_POST['rating'];
$comment = htmlspecialchars($_POST['comment']);

if ($rating < 1 || $rating > 5 || empty($comment)) {
    die("❌ Erreur : Note invalide ou commentaire vide.");
}

try {
    $bdd = getBD();
    $stmt = $bdd->prepare("INSERT INTO reviews (client_id, rating, comment, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$client_id, $rating, $comment]);

    echo "✅ Avis enregistré avec succès !";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>

