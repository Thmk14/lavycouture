<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
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
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $fonction = trim($_POST['fonction']);
    $telephone = trim($_POST['telephone']);

    if (!empty($name) && !empty($email) && !empty($password) && !empty($fonction) && !empty($telephone)) {
        $checkEmail = $pdo->prepare("SELECT id_personnel FROM personnel WHERE email = ?");
        $checkEmail->execute([$email]);

        if ($checkEmail->rowCount() > 0) {
            die("Cet email est déjà utilisé. Veuillez vous connecter.");
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO personnel(nom, email, mot_de_passe, fonction, telephone) VALUES (?, ?, ?, ?, ?)");

            try {
                $stmt->execute([$name, $email, $hashed_password, $fonction, $telephone]);

                $user_id = $pdo->lastInsertId();
                $_SESSION['id_personnel'] = $user_id;
                $_SESSION['nom'] = $name;
                $_SESSION['fonction'] = $fonction;
                setcookie("id_personnel", $user_id, time() + (30 * 24 * 60 * 60), "/");

                header("Location: listpers.php");
                
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
    <title>Ajouter client</title>
</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="container">

   
    <div class="form-box active" id="register-form" >
        <form method="POST">
            <h2 class="h2">Ajout des membres du personnel</h2>
            <input type="text" name="name" placeholder="Nom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="text" name="fonction" placeholder="Fonction" required>
            <input type="text"   name="telephone" placeholder="Téléphone" required>
            <button type="submit" name="register">Ajouter</button>
            
        </form>
    </div>
</div>


</body>
</html>

 