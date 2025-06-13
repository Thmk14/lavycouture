<?php
require 'config.php';
session_start();
// Récupérer tous les clients

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $taille = $_POST['taille'];
    $poitrine = $_POST['poitrine'];
    $hanche = $_POST['hanche'];
    $taille_de_buste = $_POST['taille_de_buste'];
    $bras = $_POST['lbras'];
    $tbras = $_POST['tbras'];
    $longueur_jambe = $_POST['longueur_jambe'];
    $cuisse = $_POST['cuisse'];
    $cou = $_POST['cou'];
    $epaule = $_POST['epaule'];
    $entrejambe = $_POST['entrejambe'];
    $total = $_POST['total'];
    

    $sql = "INSERT INTO mensuration (tour_taille, tour_poitrine, tour_hanche, taille_buste, longueur_bras, tour_bras,
                longueur_jambe, tour_cuisse, tour_cou, largeur_epaule, longueur_entrejambe, longueur_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $taille, $poitrine, $hanche, $taille_de_buste, $bras, $tbras,
        $longueur_jambe, $cuisse, $cou, $epaule, $entrejambe, $total
    ]);

    if ($result) {
    // Récupérer l'ID de la mensuration insérée
    $id_mensuration = $pdo->lastInsertId();
    $_SESSION['id_mensuration'] = $id_mensuration;

    // Rediriger vers la page précédente avec l'ID comme paramètre GET
    echo "<script>
        window.history.go(-2) ;
    </script>";
} else {
    echo "<p>❌ Une erreur est survenue lors de l’enregistrement des mesures.</p>";
}
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter des Mesures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(253, 199, 237);
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 90%;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .form-group {
            flex: 1 1 45%;
            display: flex;
            flex-direction: column;
        }

        .full-width {
            flex: 1 1 100%;
        }

        label {
            margin-bottom: 5px;
            font-size: 15px;
            color: #555;
        }

        select,
        input[type="number"],
        input[type="text"] {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            background-color: #fce3f3;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #a72872;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #882162;
        }

        @media (max-width: 768px) {
            .form-group {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Formulaire de Prise de Mesures</h1>
    <form action="mesure_manuel.php" method="POST">

        

        <div class="form-group">
            <label for="taille">Tour de taille (cm)</label>
            <input type="number" id="taille" name="taille" required>
        </div>

        <div class="form-group">
            <label for="poitrine">Tour de poitrine (cm)</label>
            <input type="number" id="poitrine" name="poitrine" required>
        </div>

        <div class="form-group">
            <label for="hanche">Tour de hanche (cm)</label>
            <input type="number" id="hanche" name="hanche" required>
        </div>

        <div class="form-group">
            <label for="taille_de_buste">Taille du buste (cm)</label>
            <input type="number" id="taille_de_buste" name="taille_de_buste" required>
        </div>

          <div class="form-group">
            <label for="lbras">Longueur de bras (cm)</label>
            <input type="number" id="lbras" name="lbras" required>
        </div>

        <div class="form-group">
            <label for="tbras">Tour du bras (cm)</label>
            <input type="number" id="tbras" name="tbras" required>
        </div>

        <div class="form-group">
            <label for="longueur_jambe">Longueur de la jambe (cm)</label>
            <input type="number" id="longueur_jambe" name="longueur_jambe" required>
        </div>

        <div class="form-group">
            <label for="cuisse">Tour de cuisse (cm)</label>
            <input type="number" id="cuisse" name="cuisse" required>
        </div>

        <div class="form-group">
            <label for="cou">Tour de cou (cm)</label>
            <input type="number" id="cou" name="cou" required>
        </div>

        <div class="form-group">
            <label for="epaule">Largeur d’épaule (cm)</label>
            <input type="number" id="epaule" name="epaule" required>
        </div>

        <div class="form-group">
            <label for="entrejambe">Longueur de l’entrejambe (cm)</label>
            <input type="number" id="entrejambe" name="entrejambe" required>
        </div>

        <div class="form-group">
            <label for="total">Longueur totale (cm)</label>
            <input type="number" id="total" name="total" required>
        </div>

        
        <div class="form-group full-width">
            <button type="submit">Enregistrer les Mesures</button>
        </div>

    </form>
</div>

</body>
</html>
