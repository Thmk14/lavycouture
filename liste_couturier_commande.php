<?php
require 'config.php';
session_start(); // Keep session_start() if other parts of the site rely on sessions.

// SQL query to fetch all validated orders for all clients
// It joins all necessary tables to get article, command, client, and mensuration details.
$sql = "SELECT c.*, art.*, cmd.*, cl.*, m.*
        FROM concerner c
        JOIN article art ON c.id_article = art.id_article
        JOIN commande cmd ON c.id_commande = cmd.id_commande
        JOIN client cl ON cmd.id_client = cl.id_client
        JOIN mensuration m ON cmd.id_mensuration = m.id_mensuration
        WHERE cmd.statut != 'En attente' AND cmd.statut != 'Annulée'   AND cmd.statut !='Prête' AND cmd.statut != 'Livrée' AND cmd.statut != 'Non livrée' AND cmd.statut != 'En cours de livraison'
        ORDER BY cl.id_client, cmd.id_commande"; // Order to facilitate grouping by client and then by command

$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group commands by client and then by command ID for better display
$grouped_by_client = [];
foreach ($commandes as $cmd) {
    $client_id = $cmd['id_client'];
    $commande_id = $cmd['id_commande'];

    if (!isset($grouped_by_client[$client_id])) {
        $grouped_by_client[$client_id] = [
            'client_info' => [
                'id_client' => $cmd['id_client'],
                'prenom' => $cmd['prenom'],
                'nom' => $cmd['nom'],
                'telephone' => $cmd['telephone'],
                'lieu_habitation' => $cmd['lieu_habitation']
            ],
            'orders' => []
        ];
    }

    if (!isset($grouped_by_client[$client_id]['orders'][$commande_id])) {
        $grouped_by_client[$client_id]['orders'][$commande_id] = [
            'commande_info' => [
                'id_commande' => $cmd['id_commande'],
                'statut' => $cmd['statut'],
                'date_commande' => $cmd['date_commande'],
                'date_livraison' => $cmd['date_livraison'],
                'total_commande' => 0 // Initialize total for this command
            ],
            'articles' => []
        ];
    }

    $item_price = $cmd['prix']; // Assuming 'prix' is the unit price of the article
    $item_quantity = $cmd['quantite'];
    $sub_total = $item_price * $item_quantity;

    $grouped_by_client[$client_id]['orders'][$commande_id]['articles'][] = [
        'image' => $cmd['image'],
        'nom_modele' => $cmd['nom_modele'],
        'quantite' => $cmd['quantite'],
        'taille_standard' => $cmd['taille_standard'],
        'tissu' => $cmd['tissu'],
        'description_modele' => $cmd['description_modele'],
        'prix' => $cmd['prix'],
        'id_commande' => $cmd['id_commande'] // Keep id_commande for mensuration link
    ];
    // Add to the total for the current command
    $grouped_by_client[$client_id]['orders'][$commande_id]['commande_info']['total_commande'] += $sub_total;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Commandes Validées</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #a72872;
            --primary-light: #fce7f3;
            --primary-dark: #8c205e;
            --accent-green: #27ae60;
            --accent-orange: #e67e22;
            --accent-grey: #7f8c8d;
            --bg-light: #f9fbfb;
            --text-dark: #333;
            --text-light: #fff;
            --shadow-light: rgba(0, 0, 0, 0.08);
            --shadow-medium: rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            padding: 20px;
            margin: 0;
            background-color: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Main container for all content */
        .main-content {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 3.5em; /* Slightly adjusted for better balance */
            margin: 40px 0 60px 0;
            text-shadow: 2px 2px 4px var(--shadow-light);
            font-weight: 700;
        }

        /* Styling for each client's section */
        .client-section {
            background-color: var(--text-light);
            border-radius: 12px;
            box-shadow: 0 8px 20px var(--shadow-medium);
            margin-bottom: 50px;
            overflow: hidden; /* Ensures border-radius applies to children */
        }

        .client-header {
            background-color: var(--primary-color);
            color: var(--text-light);
            padding: 20px 30px;
            font-size: 2.2em;
            text-align: center;
            font-weight: 600;
            letter-spacing: 0.05em;
            cursor: pointer; /* Indicate it's clickable */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .client-header .toggle-client-orders-btn { /* Renamed for clarity */
            background: none;
            border: 2px solid var(--text-light);
            color: var(--text-light);
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.7em;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .client-header .toggle-client-orders-btn:hover {
            background-color: var(--text-light);
            color: var(--primary-color);
        }

        .client-details-card {
            display: flex;
            justify-content: space-around;
            padding: 20px 30px;
            background-color: var(--primary-light);
            border-bottom: 1px solid #eee;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            gap: 15px; /* Space between items */
        }
        .client-details-card div {
            flex: 1;
            min-width: 250px; /* Ensure items don't get too small */
            text-align: center;
            font-size: 1.1em;
            color: var(--primary-dark);
        }
        .client-details-card strong {
            display: block;
            font-size: 0.9em;
            color: #777;
            margin-bottom: 5px;
        }

        /* Container for all orders within a client section, initially hidden */
        .client-orders-container {
            display: none; 
            padding-top: 10px; /* Some padding to separate from client details */
        }
        .client-orders-container.active {
            display: block;
        }


        /* Styling for each individual order within a client section */
        .order-block {
            border-top: 1px dashed #ddd; /* Dotted line to separate orders */
            padding: 25px 30px;
            background-color: #fdfdfd;
        }
        .order-block:first-of-type {
            border-top: none; /* No top border for the first order */
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-header h4 {
            color: var(--primary-dark);
            font-size: 1.8em;
            margin: 0;
            font-weight: 600;
        }
        .order-header .order-status {
            font-size: 1.1em;
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            text-transform: uppercase;
        }
        .order-status.validee { background-color: #d4edda; color: #155724; }
        .order-status.en_attente { background-color: #fff3cd; color: #856404; }
        .order-status.en_preparation { background-color: #cce5ff; color: #004085; }
        .order-status.prete { background-color: #d1ecf1; color: #0c5460; }


        .order-dates {
            display: flex;
            justify-content: space-between;
            font-size: 0.95em;
            color: #666;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .order-dates div {
            background-color: #f0f0f0;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .actions-row {
            text-align: right; /* Align action buttons to the right */
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        /* Toggle Button for Article Details */
        .toggle-details-btn {
            background-color: var(--primary-color);
            color: var(--text-light);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95em;
            margin-top: 15px;
            transition: background-color 0.3s ease;
            display: block; /* Make it a block element to center easily */
            margin-left: auto;
            margin-right: auto;
        }
        .toggle-details-btn:hover {
            background-color: var(--primary-dark);
        }

        .articles-table-container {
            display: none; /* Hidden by default */
            margin-top: 20px;
            overflow-x: auto; /* For responsive table */
        }
        .articles-table-container.active {
            display: block; /* Show when active */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--text-light);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px var(--shadow-light);
        }

        th, td {
            padding: 12px 15px;
            text-align: left; /* Align text left for better readability in tables */
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: var(--primary-light);
            color: var(--primary-dark);
            text-transform: uppercase;
            font-weight: 600;
            font-size: 0.85em;
            letter-spacing: 0.03em;
        }

        tr:nth-child(even) {
            background-color: #fafafa;
        }
        tr:hover {
            background-color: #f5f5f5;
        }

        img {
            width: 60px; /* Slightly smaller for articles table */
            height: 60px;
            border-radius: 6px;
            object-fit: cover;
            vertical-align: middle;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .img-thumbnail { /* Used for modal trigger */
            width: 80px; /* Slightly larger for main view thumbnail */
            height: 80px;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 2px 5px var(--shadow-light);
            transition: transform 0.2s ease-in-out;
        }
        .img-thumbnail:hover {
            transform: scale(1.05);
        }

        .total {
            text-align: right;
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 20px;
            font-size: 1.5em;
            padding-right: 15px;
            padding-top: 15px;
            border-top: 2px solid var(--primary-light);
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px; /* Slightly more rounded buttons */
            text-decoration: none;
            color: var(--text-light);
            border: none;
            cursor: pointer;
            font-size: 0.95em;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-block;
            margin: 5px; /* More space between action buttons */
            font-weight: 600;
            text-transform: uppercase;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-valider { background-color: var(--accent-grey); }
        .btn-valider:hover { background-color: #6c7a7b; }
        .btn-annuler { background-color: var(--accent-orange); }
        .btn-annuler:hover { background-color: #d35400; }
        .btn-prete { background-color: var(--accent-green); }
        .btn-prete:hover { background-color: #229954; }

        td form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Center buttons horizontally */
            gap: 10px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 80px; /* Adjusted padding */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.85); /* Slightly darker overlay */
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 85vh; /* Larger modal image */
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }

        .close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: #f1f1f1;
            font-size: 45px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.5);
        }
        .close:hover, .close:focus {
            color: #ccc;
            text-decoration: none;
        }

        /* Responsive adjustments */
        @media screen and (max-width: 1024px) {
            h1 { font-size: 3em; margin: 30px 0 50px 0; }
            .client-header { font-size: 1.8em; padding: 15px 20px; }
            .client-details-card div { min-width: 200px; font-size: 1em; }
            .order-block { padding: 20px 25px; }
            .order-header h4 { font-size: 1.5em; }
            th, td { padding: 10px 12px; font-size: 0.85em; }
            .img-thumbnail { width: 70px; height: 70px; }
            img { width: 50px; height: 50px; }
            .btn { padding: 8px 15px; font-size: 0.85em; }
            .total { font-size: 1.3em; }
        }

        @media screen and (max-width: 768px) {
            .main-content { padding: 10px; }
            h1 { font-size: 2.5em; margin: 25px 0 40px 0; }
            .client-section { margin-bottom: 30px; }
            .client-header { flex-direction: column; align-items: center; padding: 15px; }
            .client-header h2 { margin-bottom: 10px; }
            .client-header .toggle-client-orders-btn { margin-top: 10px; }
            .client-details-card { flex-direction: column; align-items: center; padding: 15px; }
            .client-details-card div { min-width: unset; width: 100%; text-align: left; }
            .order-block { padding: 15px 20px; }
            .order-header { flex-direction: column; align-items: flex-start; }
            .order-header h4 { font-size: 1.3em; margin-bottom: 10px; }
            .order-dates div { padding: 6px 10px; font-size: 0.9em; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            th, td { font-size: 0.8em; padding: 8px 10px; }
            .img-thumbnail { width: 60px; height: 60px; }
            img { width: 40px; height: 40px; }
            .btn { padding: 6px 12px; font-size: 0.85em; margin: 3px; }
            td form { flex-direction: column; gap: 5px; }
            .total { font-size: 1.1em; padding-right: 10px; }
            .close { font-size: 35px; top: 15px; right: 25px; }
        }

        @media screen and (max-width: 480px) {
            body { padding: 10px; }
            h1 { font-size: 2em; margin: 20px 0 30px 0; }
            .client-section { margin-bottom: 20px; }
            .client-header { font-size: 1.2em; padding: 10px 10px; }
            .order-block { padding: 10px 15px; }
            .order-header h4 { font-size: 1.1em; }
            th, td { font-size: 0.7em; padding: 6px 8px; }
            .img-thumbnail { width: 50px; height: 50px; }
            img { width: 30px; height: 30px; }
            .btn { padding: 5px 10px; font-size: 0.75em; }
            .total { font-size: 1em; padding-right: 5px; }
            .close { font-size: 30px; top: 10px; right: 20px; }
        }
    </style>
</head>
<body>

<?php include 'catmenuc.php'; ?>

<div class="main-content">
    <h1>Toutes les Commandes Validées</h1>

    <?php if (count($grouped_by_client) > 0): ?>
        <?php foreach ($grouped_by_client as $client_id => $client_data): ?>
            <div class="client-section">
                <div class="client-header">
                    <h3># <?= ($client_id) ?></h3>
                    <h3><?= ($client_data['client_info']['prenom'] . ' ' . $client_data['client_info']['nom']) ?></h3>
                    <button class="toggle-client-orders-btn" data-target="client-orders-<?= ($client_id) ?>">Afficher les commandes</button>
                </div>
                
                <div class="client-details-card">
                    <div>
                        <strong>Adresse</strong>
                        <?= ($client_data['client_info']['lieu_habitation']) ?>
                    </div>
                    <div>
                        <strong>Téléphone</strong>
                        <?= ($client_data['client_info']['telephone']) ?>
                    </div>
                </div>

                <div id="client-orders-<?= ($client_id) ?>" class="client-orders-container">
                    <?php foreach ($client_data['orders'] as $order_id => $order_data): ?>
                        <div class="order-block">
                            <div class="order-header">
                                <h4>Commande #<?= ($order_data['commande_info']['id_commande']) ?></h4>
                                <span class="order-status <?= str_replace(' ', '_', strtolower(($order_data['commande_info']['statut']))) ?>">
                                    Statut: <?= ($order_data['commande_info']['statut']) ?>
                                </span>
                            </div>
                            
                            <div class="order-dates">
                                <div><strong>Date commande:</strong> <?=($order_data['commande_info']['date_commande']) ?></div>
                                <div><strong>Date Livraison souhaitée:</strong> <?= ($order_data['commande_info']['date_livraison']) ?></div>
                            </div>

                            <div class="actions-row">
                                <form method="POST" action="marquer_prete.php">
                                    <input type="hidden" name="id_commande" value="<?= ($order_data['commande_info']['id_commande']) ?>">
                                    <button class="btn btn-valider" name="action" value="validée">Validée</button>
                                    <button class="btn btn-annuler" name="action" value="en préparation">En préparation</button>
                                    <button class="btn btn-prete" name="action" value="prête">Prête</button>
                                </form>
                            </div>

                            <button class="toggle-details-btn" data-target="articles-<?= ($order_data['commande_info']['id_commande']) ?>">
                                Voir les détails des articles
                            </button>

                            <div id="articles-<?= ($order_data['commande_info']['id_commande']) ?>" class="articles-table-container">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Image Article</th>
                                            <th>Modèle</th>
                                            <th>Image Tissu</th>
                                            <th>Quantité</th>
                                            <th>Taille</th>
                                            <th>Personnalisation</th>
                                            <th>Prix Unitaire</th>
                                            <th>Total Article</th>
                                            <th>Mensurations</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_data['articles'] as $item): ?>
                                            <?php $sous_total = $item['quantite'] * $item['prix']; ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="uploads/<?= ($item['image']) ?>" alt="Image Modèle" class="img-thumbnail">
                                                    <?php else: ?>
                                                        <span style="color:gray;">Aucune image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= ($item['nom_modele']) ?></td>
                                                <td>
                                                    <?php if (!empty($item['tissu'])): ?>
                                                        <img src="uploads/<?= ($item['tissu']) ?>" alt="Image Tissu" class="img-thumbnail">
                                                    <?php else: ?>
                                                        <span style="color:gray;">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= ($item['quantite']) ?></td>
                                                <td><?= ($item['taille_standard']) ?></td>
                                                <td><?= ($item['description_modele']) ?></td>
                                                <td><?= ($item['prix']) ?> FCFA</td>
                                                <td><?= ($sous_total) ?> FCFA</td>
                                                <td>
                                                    <a href="listmesurec.php?id_commande=<?= ($item['id_commande']) ?>" class="btn btn-prete">
                                                        Mensuration
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="total">Total de cette commande : <?= ($order_data['commande_info']['total_commande']) ?> FCFA</p>
                        </div> 
                    <?php endforeach; ?>
                </div>
            </div> 
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align:center; font-size:30px; color:var(--primary-color);">Aucune commande validée pour le moment.</p>
    <?php endif; ?>

</div>

<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
</div>

<script>
    // Modal functionality
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img01");
    document.querySelectorAll(".img-thumbnail").forEach(function(img) {
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
        };
    });
    document.querySelector(".close").onclick = function() {
        modal.style.display = "none";
    };

    // Toggle article details functionality (within each order)
    document.querySelectorAll(".toggle-details-btn").forEach(function(button) {
        button.onclick = function() {
            var targetId = this.dataset.target;
            var targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.classList.toggle("active");
                if (targetElement.classList.contains("active")) {
                    this.textContent = "Masquer les détails des articles";
                } else {
                    this.textContent = "Voir les détails des articles";
                }
            }
        };
    });

    // **NEW FUNCTIONALITY: Toggle client orders visibility**
    document.querySelectorAll(".toggle-client-orders-btn").forEach(function(button) {
        button.onclick = function() {
            var targetId = this.dataset.target;
            var targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.classList.toggle("active");
                if (targetElement.classList.contains("active")) {
                    this.textContent = "Masquer les commandes";
                } else {
                    this.textContent = "Afficher les commandes";
                }
            }
        };
    });

    // Initialize all client orders to be hidden on page load
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".client-orders-container").forEach(function(container) {
            container.classList.remove("active");
        });
        document.querySelectorAll(".toggle-client-orders-btn").forEach(function(button) {
            button.textContent = "Afficher les commandes";
        });
    });
</script>
</body>
</html>