<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

$id_personnel = $_SESSION['id_personnel'];

$requete = "SELECT * FROM personnel WHERE id_personnel = ?";
$stmt = $pdo->prepare($requete);
$stmt->execute([$id_personnel]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Lavit Couture</title>
    <style>
        * {
            box-sizing: border-box;
        }

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
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Responsive pour tablettes */
        @media screen and (max-width: 768px) {
            .profil-container {
                margin: 40px 20px;
                padding: 25px;
            }

            .profil-container h2 {
                font-size: 24px;
            }

            .profil-item label,
            .profil-item span {
                font-size: 15px;
            }
        }

        /* Responsive pour mobiles */
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

            .profil-item label,
            .profil-item span {
                width: 100%;
                text-align: left;
                font-size: 16px;
                margin-bottom: 5px;
            }

            .btn-modifier {
                font-size: 16px;
                padding: 10px;
            }

            .profil-container h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<div class="profil-container">
    <h2>Mon Profil</h2>

    <div class="profil-item">
        <label>Nom :</label> <span><?= htmlspecialchars($profil['nom']) ?></span>
    </div>

    <div class="profil-item">
        <label>Email :</label> <span><?= htmlspecialchars($profil['email']) ?></span>
    </div>

    <div class="profil-item">
        <label>Téléphone :</label> <span><?= htmlspecialchars($profil['telephone']) ?></span>
    </div>

    <div class="profil-item">
        <label>Fonction :</label> <span><?= htmlspecialchars(ucfirst($profil['fonction'])) ?></span>
    </div>

    <a href="modifier_profil_personnel.php" class="btn-modifier">Modifier mon profil</a>
</div>

</body>
</html>
