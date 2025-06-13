<?php
require 'config.php';



if (isset($_GET['id_lca']) && is_numeric($_GET['id_lca'])) {
    $id_lca = (int) $_GET['id_lca'];

    // Récupérer la mensuration liée à l'article commandé
    $sql = "SELECT m.tour_taille, m.tour_poitrine, m.tour_hanche, m.taille_buste, m.longueur_bras, m.tour_bras,
                m.longueur_jambe, m.tour_cuisse, m.tour_cou, m.largeur_epaule, m.longueur_entrejambe, m.longueur_total
            FROM mensuration m
            INNER JOIN lien_commande_article lca ON m.id_mensuration = lca.id_mensuration
            WHERE lca.id_lca = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_lca]);
    $mesure = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    header('Location: panier.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mesures du Client</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Styles inchangés */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f2f7;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 700px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #a72872;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background: #a72872;
            color: white;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            text-decoration: none;
            padding: 10px 20px;
            background: #a72872;
            color: white;
            border-radius: 5px;
            display: inline-block;
            transition: background 0.3s;
        }

        .back a:hover {
            background: #8c1f5f;
        }

        @media screen and (max-width: 600px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 15px;
            }

            table, th, td {
                font-size: 14px;
            }

            h1 {
                font-size: 20px;
            }

            .back a {
                width: 100%;
                padding: 12px 0;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Mesures du Client</h1>

    <?php if (!$mesure): ?>
        <p style="text-align:center; font-size:20px; color:gray;">Aucune mesure enregistrée pour ce client.</p>
    
        
    <?php elseif ($mesure) : ?>
       <table>
            <tr><th>Tour de Taille</th><td><?= htmlspecialchars($mesure['tour_taille'] ?? '') ?> cm</td></tr>
            <tr><th>Tour de Poitrine</th><td><?= htmlspecialchars($mesure['tour_poitrine'] ?? '') ?> cm</td></tr>
            <tr><th>Tour de Hanche</th><td><?= htmlspecialchars($mesure['tour_hanche'] ?? '') ?> cm</td></tr>
            <tr><th>Taille du Buste</th><td><?= htmlspecialchars($mesure['taille_buste'] ?? '') ?> cm</td></tr>
            <tr><th>Longueur du Bras</th><td><?= htmlspecialchars($mesure['longueur_bras'] ?? '') ?> cm</td></tr>
            <tr><th>Tour de Bras</th><td><?= htmlspecialchars($mesure['tour_bras'] ?? '') ?> cm</td></tr>
            <tr><th>Longueur de Jambe</th><td><?= htmlspecialchars($mesure['longueur_jambe'] ?? '') ?> cm</td></tr>
            <tr><th>Tour de Cuisse</th><td><?= htmlspecialchars($mesure['tour_cuisse'] ?? '') ?> cm</td></tr>
            <tr><th>Tour de Cou</th><td><?= htmlspecialchars($mesure['tour_cou'] ?? '') ?> cm</td></tr>
            <tr><th>Largeur des Épaules</th><td><?= htmlspecialchars($mesure['largeur_epaule'] ?? '') ?> cm</td></tr>
            <tr><th>Longueur Entrejambe</th><td><?= htmlspecialchars($mesure['longueur_entrejambe'] ?? '') ?> cm</td></tr>
            <tr><th>Longueur Totale</th><td><?= htmlspecialchars($mesure['longueur_total'] ?? '') ?> cm</td></tr>
        </table>
        <?php endif; ?>

    <div class="back">
        <a href="panier.php">Retour aux commandes</a>
    </div>
</div>

</body>
</html>
