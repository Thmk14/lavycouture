<?php
require 'config.php'; // Fichier connexion à la base

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $tel= $_POST['tel'];
    $hbt = $_POST['hbt'];
    $lieu_prise = $_POST['lieu_prise'];
    $taille = $_POST['taille'];
    $poitrine = $_POST['poitrine'];
    $hanche = $_POST['hanche'];
    $taille_de_buste = $_POST['taille_de_buste'];
    $bras = $_POST['bras'];
    $tbras = $_POST['tbras'];
    $longueur_jambe = $_POST['longueur_jambe'];
    $cuisse = $_POST['cuisse'];
    $cou = $_POST['cou'];
    $epaule = $_POST['epaule'];
    $entrejambe = $_POST['entrejambe'];
    $total = $_POST['total'];

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // 1. Insertion dans client
        $insertClient = $pdo->prepare("INSERT INTO client (nom, prenom, telephone, lieu_habitation) VALUES (?, ?,?,?)");
        $insertClient->execute([$nom,$prenom,$tel,$hbt]);

        // Récupérer l'ID du client inséré
        $id_client = $pdo->lastInsertId();

        // 2. Insertion dans mensuration
        $insertMensuration = $pdo->prepare("INSERT INTO mensuration (
            tour_taille, tour_poitrine, tour_hanche, taille_buste, longueur_bras, tour_bras,
            longueur_jambe, tour_cuisse, tour_cou, longueur_epaule, longueur_entrejambe,
            longueur_total, lieu_prise, id_client
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $insertMensuration->execute([
            $taille, $poitrine, $hanche, $taille_de_buste, $bras, $tbras,
            $longueur_jambe, $cuisse, $cou, $epaule, $entrejambe,
            $total, $lieu_prise, $id_client
        ]);

        // Valider la transaction
        $pdo->commit();

        echo "<script>alert('✅ Client et mesures enregistrés avec succès !'); window.history.go(-2);</script>";

    } catch (Exception $e) {
        // Annuler en cas d'erreur
        $pdo->rollBack();
        echo "<p>❌ Erreur : " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Prise de Mesures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(253, 199, 237);
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 80%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #555;
        }
        
        select,
        input[type="number"],
        input[type="text"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
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
        }

        button:hover {
            background-color: #882162;
        }

        @media (max-width: 768px) {
            .form-container {
                width: 95%;
            }
        }
    </style>
</head>
<body>



<h1>Formulaire de Prise de Mesures</h1>

<div class="form-container">
    
    <form action="ajoutmesure.php" method="POST">

    
        <input type="text" id="nom" name="nom" placeholder="Nom " required>

        <input type="text" id="nom" name="prenom" placeholder=" Prénoms" required>

        <input type="text" id="tel"  name="tel" placeholder="Téléphone" required>

        <input type="text" id="hbt" name="hbt" placeholder="Lieu d'habitation" required>


        <label for="lieu_prise">Lieu de prise</label>
        <select name="lieu_prise" id="">
            <option value="Via l'application">Via l'application</option>
            <option value="A l'atelier">A l'atelier</option>
        </select>
        

        <label for="taille">Tour de taille (cm)</label>
        <input type="number" id="taille" name="taille" required>

        <label for="poitrine">Tour de poitrine (cm)</label>
        <input type="number" id="poitrine" name="poitrine" required>

        <label for="hanche">Tour de hanche (cm)</label>
        <input type="number" id="hanche" name="hanche" required>

        <label for="taille_de_buste">Taille du buste (cm)</label>
        <input type="number" id="taille_de_buste" name="taille_de_buste" required>

        <label for="bras">Longueur du bras (cm)</label>
        <input type="number" id="bras" name="bras" required>

        <label for="tbras">Tour de bras (cm)</label>
        <input type="number" id="tbras" name="tbras" required>

        <label for="longueur_jambe">Longueur de jambe (cm)</label>
        <input type="number" id="longueur_jambe" name="longueur_jambe" required>

        <label for="cuisse">Tour de cuisse (cm)</label>
        <input type="number" id="cuisse" name="cuisse" required>

        <label for="cou">Tour de cou (cm)</label>
        <input type="number" id="cou" name="cou" required>

        <label for="epaule">Largeur des épaules (cm)</label>
        <input type="number" id="epaule" name="epaule" required>

        <label for="entrejambe">Longueur de l'entrejambe (cm)</label>
        <input type="number" id="entrejambe" name="entrejambe" required>

        <label for="total">Longueur totale (cm)</label>
        <input type="number" id="total" name="total" required>



        <button type="submit">Soumettre les Mesures</button>
    </form>
</div>

</body>
</html>
