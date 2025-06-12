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

// GESTION DE LA CONNEXION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id_personnel, nom, fonction, mot_de_passe FROM personnel WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['id_personnel'] = $user['id_personnel'];
            $_SESSION['fonction'] = $user['fonction'];
            $_SESSION['nom'] = $user['nom'];

            setcookie("id_personnel", $user['id_personnel'], time() + (30 * 24 * 60 * 60), "/");

            // Redirection selon la fonction
            switch (strtolower($user['fonction'])) {
                case 'administrateur':
                    header("Location: admin.php");
                    break;
                case 'couturier':
                    header("Location: couturier.php");
                    break;
                case 'livreur':
                    header("Location: livreur.php");
                    break;
                default:
                    $error_message = "Fonction inconnue. Veuillez contacter l'administrateur.";
            }
        } else {
            $error_message = "Email ou mot de passe incorrect.";
        }
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
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
            $error_message = "Cet email est déjà utilisé. Veuillez vous connecter.";
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

                header("Location: personnelog.php");
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

<!-- HTML (identique avec ajustement sur les noms des champs) -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion & Inscription</title>
    <link rel="stylesheet" href="css/connexion.css">
</head>
<body>

<div class="container">
    <?php if (!empty($error_message)): ?>
        <p class="alert error"><?= htmlspecialchars($error_message) ?></p>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <p class="alert success"><?= htmlspecialchars($success_message) ?></p>
    <?php endif; ?>

    <!-- Connexion -->
    <div class="form-box active" id="login-form">
        <form action="" method="POST">
            <h2 class="h2">Se connecter</h2>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <button type="submit" name="login">Se connecter</button>
            <!-- <p><a href="#" onclick="showForm('register-form')">S'inscrire</a></p> -->
        </form>
    </div>

    <!-- Inscription -->
    <div class="form-box" id="register-form">
        <form action="" method="POST">
            <h2 class="h2">S'inscrire</h2>
            <input type="text" name="name" placeholder="Nom" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="text" name="fonction" placeholder="Fonction" required>
            <input type="text" name="telephone" placeholder="Téléphone" required>
            <button type="submit" name="register">S'inscrire</button>
            
        </form>
    </div>
</div>

<script>
function showForm(id) {
    document.getElementById('login-form').classList.remove('active');
    document.getElementById('register-form').classList.remove('active');
    document.getElementById(id).classList.add('active');
}
</script>

</body>
</html>
