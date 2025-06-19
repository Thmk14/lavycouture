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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $telephone = trim($_POST['telephone']);

    if (!empty($name) && !empty($lastname) && !empty($email) && !empty($password) && !empty($telephone)) {
        // Vérifier si l'email existe déjà
        $checkEmail = $pdo->prepare("SELECT id_livreur FROM livreur WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            die("Cet email est déjà utilisé. Veuillez vous connecter.");
        } else {
            // Hasher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO livreur (nom, prenom, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?)");
            try {
                $stmt->execute([$name, $lastname, $email, $hashed_password, $telephone]);

                // Récupérer l'ID du nouvel utilisateur pour le connecter immédiatement
                $user_id = $pdo->lastInsertId();
                $_SESSION['id'] = $user_id;
                setcookie("id", $user_id, time() + (30 * 24 * 60 * 60), "/"); // Cookie 30 jours

                $success_message = "Inscription réussie ! Vous êtes connecté.";
                header("Location: listlivreur.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Erreur lors de l'inscription : " . $e->getMessage();
            }
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
    <title>Ajouter livreur</title>
</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="container">

    <!-- FORMULAIRE D'INSCRIPTION -->
    <div class="form-box active" id="register-form">
        <form method="POST">
            <h2 class="h2">Ajouter livreur</h2>
            <input type="text" name="name" placeholder="Nom" required>
            <input type="text" name="lastname" placeholder="Prénom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="text" name="telephone" placeholder="Téléphone" required>
            <button type="submit" name="register">Ajouter</button>
            
        </form>
    </div>
</div>


</body>
</html>

 