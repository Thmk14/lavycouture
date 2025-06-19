<?php
// === SESSION PERSISTANTE (1 AN) ===
$one_year = 365 * 24 * 60 * 60; // 1 an en secondes
ini_set('session.gc_maxlifetime', $one_year);
session_set_cookie_params($one_year);

// Démarrer la session
session_start();

require 'config.php';

$error = "";
$success = "";

// ===== INSCRIPTION CLIENT =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $mot_de_passe = trim($_POST['mot_de_passe']);
    $confirmer = trim($_POST['confirmer']);

    if ($nom && $prenom && $email && $telephone && $mot_de_passe && $confirmer) {
        if ($mot_de_passe === $confirmer) {
            $check = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() == 0) {
                $hashed = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $hashed, $telephone]);

                $success = "Inscription réussie ! Vous pouvez vous connecter.";
            } else {
                $error = "Cet email est déjà utilisé.";
            }
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}

// ===== CONNEXION MULTI-TABLES =====
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $mot_de_passe = trim($_POST['mot_de_passe']);

    if ($email && $mot_de_passe) {
        $roles = [
            'client' => ['table' => 'client', 'id_field' => 'id_client', 'redirect' => 'index.php'],
            'couturier' => ['table' => 'couturier', 'id_field' => 'id_couturier', 'redirect' => 'dashboard_couturier.php'],
            'livreur' => ['table' => 'livreur', 'id_field' => 'id_livreur', 'redirect' => 'dashboard_livreur.php'],
            'administrateur' => ['table' => 'administrateur', 'id_field' => 'id_admin', 'redirect' => 'admin.php']
        ];

        foreach ($roles as $role => $data) {
            $stmt = $pdo->prepare("SELECT * FROM {$data['table']} WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
                session_regenerate_id(true); // Sécurité

                $_SESSION['id'] = $user[$data['id_field']];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['type_utilisateur'] = $role;

                header("Location: {$data['redirect']}");
                exit();
            }
        }

        $error = "Email ou mot de passe incorrect.";
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/connexion.css">
    <title>Connexion & Inscription</title>
</head>
<body>

<div class="container">
    

    <!-- FORMULAIRE DE CONNEXION -->
    <div class="form-box active" id="login-form">

        <!-- Affichage des messages d'erreur ou de succès -->
    <?php if (!empty($error) && isset($_POST['login'])): ?>
      <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success) && isset($_POST['login']) == false): ?>
      <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

        <form action="" method="POST">
            <h2 class="h2">Se connecter</h2>
            
              <input type="email" name="email" placeholder="Email" required><br>
              <input type="password" name="mot_de_passe" placeholder="Mot de passe" required><br>
              <button type="submit" name="login">Se connecter</button>

            <p>Vous n'avez pas de compte ? <a href="#" onclick="showForm('register-form')">S'inscrire</a></p>
        </form>
    </div>

    <!-- FORMULAIRE D'INSCRIPTION -->
    <div class="form-box" id="register-form">

        <form action="" method="POST">
            <h2 class="h2">S'inscrire</h2>
            <input type="text" name="nom" placeholder="Nom" required><br>
            <input type="text" name="prenom" placeholder="Prénom" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="telephone" placeholder="Téléphone" required><br>
            <input type="password" name="mot_de_passe" placeholder="Mot de passe" required><br>
            <input type="password" name="confirmer" placeholder="Confirmer le mot de passe" required><br>
            <button type="submit" name="register">S'inscrire</button>
            <p>Vous avez déjà un compte ? <a href="#" onclick="showForm('login-form')">Se connecter</a></p>
        </form>
    </div>
</div>

<script src="js/script.js"></script>

</body>
</html>

 