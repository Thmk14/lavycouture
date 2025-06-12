<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
$id_client = $_SESSION['id_client'];

// Récupérer les infos actuelles
$requete = "SELECT * FROM client WHERE id_client = ?";
$stmt = $pdo->prepare($requete);
$stmt->execute([$id_client]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $telephone = htmlspecialchars($_POST['telephone']);
    $adresse = htmlspecialchars($_POST['lieu_habitation']);

    $update = "UPDATE client SET nom = ?, prenom = ?, email = ?, telephone = ?, lieu_habitation = ? WHERE id_client = ?";
    $stmt = $pdo->prepare($update);
    $stmt->execute([$nom, $prenom, $email, $telephone, $adresse, $id_client]);

    header('Location: profil_client.php');
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
        .form-container {
            max-width: 500px;
            margin: 60px auto;
            padding: 30px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            color: #d63384;
            margin-bottom: 30px;
        }
        form label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        form input[type="text"],
        form input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        .btn-save {
            width: 100%;
            padding: 12px;
            background-color: #d63384;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-save:hover {
            background-color: #c02670;
        }

          /* Responsive pour mobile */
          @media screen and (max-width: 480px) {
            .form-container {
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            color: #d63384;
            margin-bottom: 30px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="email"] {
            width: 80%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        }

    </style>
</head>
<body>

<div class="form-container">
    <h2>Modifier mon Profil</h2>

    <form action="" method="post">
        <label>Nom :</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($profil['nom']) ?>" required>

        <label>Prénom :</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($profil['prenom']) ?>" required>

        <label>Email :</label>
        <input type="email" name="email" value="<?= htmlspecialchars($profil['email']) ?>" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($profil['telephone']) ?>" required>

        <label>Adresse :</label>
        <input type="text" name="lieu_habitation" value="<?= htmlspecialchars($profil['lieu_habitation']) ?>" required>

        <button type="submit" class="btn-save">Enregistrer les modifications</button>
    </form>
</div>

</body>
</html>
