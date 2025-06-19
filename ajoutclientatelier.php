<?php
session_start();
require 'config.php'; // Connexion à la base de données

$error_message = "";
$success_message = "";

// Vérification de la connexion à la base
if (!isset($pdo)) {
    die("Erreur de connexion à la base de données.");
}


// GESTION DE L'INSCRIPTION 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $telephone = trim($_POST['telephone']);
     $lieu = trim($_POST['lieu']);

    if (!empty($name) && !empty($lastname)  && !empty($telephone) && !empty($lieu)) {
      
        
            $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, telephone, lieu_habitation) VALUES ( ?, ?, ?, ?)");
            try {
                $stmt->execute([$name, $lastname,  $telephone, $lieu]);

                // Récupérer l'ID du nouvel utilisateur pour le connecter immédiatement
                $user_id = $pdo->lastInsertId();
                $_SESSION['id'] = $user_id;
                setcookie("id", $user_id, time() + (30 * 24 * 60 * 60), "/"); // Cookie 30 jours

                $success_message = "Inscription réussie ! Vous êtes connecté.";
                header("Location: client_atelier.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Erreur lors de l'inscription : " . $e->getMessage();
            }
        
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/connexion.css">
    <title>Ajouter client</title>
</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="container">

    <!-- FORMULAIRE D'INSCRIPTION -->
    <div class="form-box active" id="register-form">
        <form method="POST">
            <h2 class="h2">Ajouter client</h2>
            <input type="text" name="name" placeholder="Nom" required>
            <input type="text" name="lastname" placeholder="Prénom" required>
            <input type="text" name="telephone" placeholder="Téléphone" required>
             <input type="text" name="lieu" placeholder="Lieu d'habitation" required>
            <button type="submit" name="register">Ajouter</button>
            
        </form>
    </div>
</div>


</body>
</html>

 