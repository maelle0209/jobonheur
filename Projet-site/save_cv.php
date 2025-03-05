<?php
session_start();
include 'bd.php';

if (!isset($_SESSION['client_id']) || !is_numeric($_SESSION['client_id'])) {
    die("Erreur : ID client invalide.");
}

$id_client = (int) $_SESSION['client_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les informations soumises
    $name = $_POST['name'];
    $title = $_POST['title'];
    $age_category = $_POST['age_category'];
    $education_level = $_POST['education_level'];
    $education = $_POST['education'];
    $experience = $_POST['experience'];
    $skills = $_POST['skills'];
    $languages = $_POST['languages'];
    $contact = $_POST['contact'];
    
    // Gérer la photo (si elle est envoyée)
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_path = 'uploads/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    try {
        $bdd = getBD();

        // Vérifier si un CV existe déjà pour ce client
        $sql_check = "SELECT id FROM cv WHERE id_client = ?";
        $stmt_check = $bdd->prepare($sql_check);
        $stmt_check->bindParam(1, $id_client, PDO::PARAM_INT);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            // Le CV existe déjà, donc on met à jour
            $sql_update = "UPDATE cv SET name = ?, title = ?, age_category = ?, education_level = ?, education = ?, experience = ?, skills = ?, languages = ?, contact = ?, photo_path = ? WHERE id_client = ?";
            $stmt_update = $bdd->prepare($sql_update);
            $stmt_update->bindParam(1, $name, PDO::PARAM_STR);
            $stmt_update->bindParam(2, $title, PDO::PARAM_STR);
            $stmt_update->bindParam(3, $age_category, PDO::PARAM_STR);
            $stmt_update->bindParam(4, $education_level, PDO::PARAM_STR);
            $stmt_update->bindParam(5, $education, PDO::PARAM_STR);
            $stmt_update->bindParam(6, $experience, PDO::PARAM_STR);
            $stmt_update->bindParam(7, $skills, PDO::PARAM_STR);
            $stmt_update->bindParam(8, $languages, PDO::PARAM_STR);
            $stmt_update->bindParam(9, $contact, PDO::PARAM_STR);
            $stmt_update->bindParam(10, $photo_path, PDO::PARAM_STR);
            $stmt_update->bindParam(11, $id_client, PDO::PARAM_INT);
            $stmt_update->execute();

            echo "CV mis à jour avec succès !";
        } else {
            // Le CV n'existe pas, on l'insère
            $sql_insert = "INSERT INTO cv (name, title, age_category, education_level, education, experience, skills, languages, contact, photo_path, id_client) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert = $bdd->prepare($sql_insert);
            $stmt_insert->bindParam(1, $name, PDO::PARAM_STR);
            $stmt_insert->bindParam(2, $title, PDO::PARAM_STR);
            $stmt_insert->bindParam(3, $age_category, PDO::PARAM_STR);
            $stmt_insert->bindParam(4, $education_level, PDO::PARAM_STR);
            $stmt_insert->bindParam(5, $education, PDO::PARAM_STR);
            $stmt_insert->bindParam(6, $experience, PDO::PARAM_STR);
            $stmt_insert->bindParam(7, $skills, PDO::PARAM_STR);
            $stmt_insert->bindParam(8, $languages, PDO::PARAM_STR);
            $stmt_insert->bindParam(9, $contact, PDO::PARAM_STR);
            $stmt_insert->bindParam(10, $photo_path, PDO::PARAM_STR);
            $stmt_insert->bindParam(11, $id_client, PDO::PARAM_INT);
            $stmt_insert->execute();

            echo "CV enregistré avec succès !";
        }

        // Rediriger après un délai
        header('refresh:2; url=cv.php');
        exit();  // Stopper l'exécution du script après la redirection
    } catch (PDOException $e) {
        echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
} else {
    echo "Aucune donnée reçue.";
}
?>
