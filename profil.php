<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';

$id = $_SESSION['id'];
$role = $_SESSION['role'];

if($role == 'client') {
    $role_trouve = 'client';
} elseif ($role == 'couturier') {
    $role_trouve = 'couturier';
} elseif ($role == 'livreur') {
    $role_trouve = 'livreur';
} elseif ($role == 'administrateur') {
    $role_trouve = 'administrateur';
} else {
    die("Rôle non reconnu.");
}
$roles = [
    'client' => ['table' => 'client', 'id_colonne' => 'id_client'],
    'couturier' => ['table' => 'couturier', 'id_colonne' => 'id_couturier'],
    'livreur' => ['table' => 'livreur', 'id_colonne' => 'id_livreur'],
    'administrateur' => ['table' => 'administrateur', 'id_colonne' => 'id_admin']
];

if (!isset($roles[$role])) {
    die("Rôle non reconnu.");
}

$table = $roles[$role]['table'];
$id_col = $roles[$role]['id_colonne'];

$sql = "SELECT * FROM $table WHERE $id_col = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profil) {
    die("Profil introuvable.");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Lavy Couture</title>
    <style>
        * { box-sizing: border-box; }
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
            font-size: 28px;
        }
        .profil-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .profil-item label {
            font-weight: bold;
            color: #333;
            width: 40%;
            font-size: 16px;
        }
        .profil-item span {
            color: #666;
            font-size: 16px;
            width: 55%;
            text-align: right;
            word-wrap: break-word;
        }
        .btn-modifier {
            display: block;
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            text-align: center;
            background-color: #d63384;
            color: white;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }
        .btn-modifier:hover {
            background-color: #c02670;
        }

        @media screen and (max-width: 768px) {
            .profil-container { margin: 40px 20px; padding: 25px; }
            .profil-container h2 { font-size: 24px; }
            .profil-item label, .profil-item span { font-size: 15px; }
        }
        @media screen and (max-width: 480px) {
            .profil-container {
                margin: 20px 10px;
                padding: 20px;
                border-radius: 10px;
                width: calc(100% - 20px);
            }
            .profil-item {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 15px;
            }
            .profil-item label, .profil-item span {
                width: 100%;
                text-align: left;
                font-size: 16px;
                margin-bottom: 5px;
            }
            .profil-container h2 { font-size: 22px; }
            .btn-modifier { font-size: 16px; padding: 10px; }
        }
    </style>
</head>
<body>

<div class="profil-container">
    <h2>Mon Profil </h2>

    <?php if (isset($profil['nom'])): ?>
        <div class="profil-item">
            <label>Nom :</label> <span><?= htmlspecialchars($profil['nom']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($profil['prenom'])): ?>
        <div class="profil-item">
            <label>Prénom :</label> <span><?= htmlspecialchars($profil['prenom']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($profil['email'])): ?>
        <div class="profil-item">
            <label>Email :</label> <span><?= htmlspecialchars($profil['email']) ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($profil['telephone'])): ?>
        <div class="profil-item">
            <label>Téléphone :</label> <span><?= htmlspecialchars($profil['telephone']) ?></span>
        </div>
    <?php endif; ?>

    <a href="modifier_profil.php?role=<?= urlencode($role_trouve) ?>" class="btn-modifier">Modifier mon profil</a>
</div>

</body>
</html>
