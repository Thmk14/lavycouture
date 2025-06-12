<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php'; // Connexion BDD

// Vérification de connexion
if (!isset($_SESSION['id_personnel'])) {
    header('Location: personnelog.php');
    exit();
}

// Récupérer les infos actuelles
$id_personnel = $_SESSION['id_personnel'];

$requete = "SELECT * FROM personnel WHERE id_personnel = ?";
$stmt = $pdo->prepare($requete);
$stmt->execute([$id_personnel]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];

    // Mettre à jour les informations
    $update = "UPDATE personnel SET nom = ?, email = ?, telephone = ? WHERE id_personnel = ?";
    $stmt = $pdo->prepare($update);
    $stmt->execute([$nom, $email, $telephone, $id_personnel]);

    // Redirection après modification
    header('Location: profil_personnel.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Profil - Lavit Couture</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fdeaf5;
            margin: 0;
            padding: 0;
        }
        .profil-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .profil-container h2 {
            text-align: center;
            color: #d63384;
            margin-bottom: 30px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input {
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-valider {
            padding: 12px;
            background-color: #d63384;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-valider:hover {
            background-color: #c02670;
        }

          /* Responsive pour mobile */
          @media screen and (max-width: 480px) {
            .profil-container {
                margin: 20px 10px;
                width: calc(100% - 20px);
                padding: 20px;
                border-radius: 10px;
            }
            .profil-item {
                flex-direction: column;
                align-items: flex-start;
            }
            .profil-item label, .profil-item span {
                width: 100%;
                text-align: left;
                font-size: 16px;
            }

        
        }
    </style>
</head>
<body>

<div class="profil-container">
    <h2>Modifier mon profil</h2>

    <form method="POST">
        <label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($profil['nom']) ?>" required>

        <label for="email">Email :</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($profil['email']) ?>" required>

        <label for="telephone">Téléphone :</label>
        <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($profil['telephone']) ?>" required>

        <button type="submit" class="btn-valider">Enregistrer les modifications</button>
    </form>
</div>

</body>
</html>
