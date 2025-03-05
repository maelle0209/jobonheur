<?php
session_start();
include 'bd.php';  // Connexion à la base de données
$bdd = getBD();  

header('Content-Type: application/json'); // Réponse en JSON

// Lire les données JSON envoyées via POST
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si l'ID client est dans les données envoyées
if (isset($data['id_client']) && !empty($data['id_client'])) {
    $id_client = $data['id_client'];
} elseif (isset($_SESSION['client_id'])) {
    // Si l'ID client n'est pas dans la requête POST, vérifier dans la session
    $id_client = $_SESSION['client_id'];
} else {
    // Si l'ID client n'est pas fourni ni en POST ni en session
    echo json_encode(['error' => 'ID client non fourni']);
    exit();
}

try {
    $query = "SELECT clients.sexe, cv.age_category, cv.education_level
              FROM clients
              JOIN cv ON clients.id_client = cv.id_client
              WHERE clients.id_client = :id_client LIMIT 1";

    $stmt = $bdd->prepare($query);
    $stmt->bindParam(':id_client', $id_client, PDO::PARAM_INT);
    $stmt->execute();

    // Vérifier si des données sont retournées
    if ($stmt->rowCount() > 0) {
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));  // Afficher les données du client
    } else {
        echo json_encode(['error' => 'Client non trouvé']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erreur SQL: ' . $e->getMessage()]);
}
?>
