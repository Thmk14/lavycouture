<?php
require 'session.php';
require 'config.php';

// Vérification du paramètre id_mensuration dans l'URL
if (!isset($_GET['id_mensuration']) || !is_numeric($_GET['id_mensuration'])) {
    exit("Mensuration invalide.");
}

$id_mensuration = (int)$_GET['id_mensuration'];

// Récupérer les mensurations existantes
$stmt = $pdo->prepare("SELECT * FROM mensuration WHERE id_mensuration = ?");
$stmt->execute([$id_mensuration]);
$mensuration = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$mensuration) {
    exit("Mensuration non trouvée.");
}

// Traitement du formulaire en POST (mise à jour)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération et sécurisation des données envoyées
    $tour_taille = $_POST['tour_taille'] ?? '';
    $tour_hanche = $_POST['tour_hanche'] ?? '';
    $tour_poitrine = $_POST['tour_poitrine'] ?? '';
    $longueur_manche = $_POST['longueur_manche'] ?? '';
    $epaule = $_POST['epaule'] ?? '';

    // Ici tu peux ajouter des validations plus poussées si besoin

    // Mise à jour des mensurations dans la base
    $stmt = $pdo->prepare("
        UPDATE mensuration 
        SET tour_taille = ?, tour_hanche = ?, tour_poitrine = ?, longueur_manche = ?, epaule = ?
        WHERE id_mensuration = ?
    ");
    $stmt->execute([$tour_taille, $tour_hanche, $tour_poitrine, $longueur_manche, $epaule, $id_mensuration]);

    echo "<script>alert('Mensuration mise à jour avec succès !'); window.location.href='panier.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Mensuration</title>
    <style>
        label {
            display: block;
            margin-top: 10px;
        }
        input[type=text] {
            width: 250px;
            padding: 5px;
        }
        button {
            margin-top: 15px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        form {
            max-width: 400px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Modifier les Mensurations</h2>
    <form method="POST" action="">
        <label for="tour_taille">Tour de taille :</label>
        <input type="text" id="tour_taille" name="tour_taille" value="<?= htmlspecialchars($mensuration['tour_taille']) ?>" required>

        <label for="tour_hanche">Tour de hanche :</label>
        <input type="text" id="tour_hanche" name="tour_hanche" value="<?= htmlspecialchars($mensuration['tour_hanche']) ?>" required>

        <label for="tour_poitrine">Tour de poitrine :</label>
        <input type="text" id="tour_poitrine" name="tour_poitrine" value="<?= htmlspecialchars($mensuration['tour_poitrine']) ?>">

        <label for="longueur_manche">Longueur de manche :</label>
        <input type="text" id="longueur_manche" name="longueur_manche" value="<?= htmlspecialchars($mensuration['longueur_manche']) ?>">

        <label for="epaule">Épaule :</label>
        <input type="text" id="epaule" name="epaule" value="<?= htmlspecialchars($mensuration['epaule']) ?>">

        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
