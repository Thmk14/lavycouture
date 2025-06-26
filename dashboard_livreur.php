<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

// Vérifie que l'utilisateur est connecté en tant que personnel (livreur)
if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit();
}

$id_livreur = $_SESSION['id']; // Renamed to avoid conflict with order ID

// Si un client est sélectionné via AJAX, renvoyer les données en JSON
if (isset($_GET['id_client']) && !empty($_GET['id_client'])) {
    $id_client = intval($_GET['id_client']);
    
    try {
        // Infos client
        $stmt_info = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
        $stmt_info->execute([$id_client]);
        $client_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

        if (!$client_info) {
            echo json_encode(['error' => 'Client introuvable']);
            exit;
        }

        // Récupérer toutes les commandes du client, y compris statut_livraison et date_livraison
        $sql = "SELECT c.*, art.*, cmd.*, m.*
                FROM concerner c
                JOIN article art ON c.id_article = art.id_article
                JOIN commande cmd ON c.id_commande = cmd.id_commande
                JOIN mensuration m ON cmd.id_mensuration = m.id_mensuration
                WHERE cmd.id_client = ? 
                ORDER BY cmd.date_commande DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_client]);
        $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Regrouper les articles par commande
        $grouped = [];
        foreach ($commandes as $row) {
            $id_commande = $row['id_commande'];
            if (!isset($grouped[$id_commande])) {
                $grouped[$id_commande] = [
                    'commande_details' => $row,
                    'articles' => []
                ];
            }
            $grouped[$id_commande]['articles'][] = $row;
        }

        echo json_encode([
            'client_info' => $client_info,
            'commandes' => $grouped
        ]);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erreur de base de données : ' . $e->getMessage()]);
        exit;
    }
}

// If the livreur marks the command with a delivery status
if (isset($_GET['id_commande']) && isset($_GET['statut'])) {
    $id_commande = $_GET['id_commande'];
    $statut = $_GET['statut'];

    // Update the 'statut_livraison' column in the 'commande' table
    $update = $pdo->prepare("UPDATE commande SET statut = ? WHERE id_commande = ?");
    $update->execute([$statut, $id_commande]);

    // Redirige après la mise à jour
    header('Location: dashboard_livreur.php');
    exit();
}

// Handle date livraison update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande']) && isset($_POST['date_livraison'])) {
    $id_commande_update = $_POST['id_commande'];
    $new_date_livraison = $_POST['date_livraison'];

    $update_date_sql = "UPDATE commande SET date_livraison = ? WHERE id_commande = ?";
    $stmt_update_date = $pdo->prepare($update_date_sql);
    $stmt_update_date->execute([$new_date_livraison, $id_commande_update]);

    header('Location: dashboard_livreur.php');
    exit();
}


// Requête pour récupérer les commandes prêtes à livrer avec les infos clients
$sql = "SELECT DISTINCT cl.id_client, cl.nom, cl.prenom, cl.telephone, cl.pays, cl.ville, cl.lieu_habitation,
                COUNT(DISTINCT cmd.id_commande) as nb_commandes,
                SUM(cmd.montant_total) as total_commande
        FROM client cl
        JOIN commande cmd ON cl.id_client = cmd.id_client
       WHERE  cmd.statut = 'Prête' AND cmd.statut !='Validée ' AND cmd.statut != 'Annulée' AND cmd.statut != 'En attente' AND cmd.statut != 'En préparation' AND cmd.statut != 'Livrée'

        GROUP BY cl.id_client
        ORDER BY cl.nom, cl.prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Livreur</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #a72872;
            --secondary-color: #db2e8b;
            --accent-color: #fce7f3;
            --text-color: #333;
            --light-text-color: #555;
            --background-color: #f9f9f9;
            --card-background: #ffffff;
            --border-color: #eee;
            --success-color: #5cb85c;
            --warning-color: #f0ad4e;
            --info-color: #5bc0de;
            --danger-color: #d9534f;
            --in-progress-color: #ffc107; /* New color for 'En cours de livraison' */
        }

        body {
            font-family: 'Poppins', sans-serif;
            padding: 30px;
            background-color: var(--background-color);
            margin: 0;
            color: var(--text-color);
            line-height: 1.6;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 3em;
            margin: 50px 0;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }

        .clients-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .client-card {
            background-color: var(--card-background);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 5px solid var(--primary-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .client-card h3 {
            color: var(--primary-color);
            margin: 0 0 15px 0;
            font-size: 1.4em;
            font-weight: 600;
        }

        .client-card p {
            margin: 8px 0;
            color: var(--light-text-color);
            font-size: 1em;
        }

        .client-card strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5em;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.9em;
            color: var(--light-text-color);
            margin-top: 5px;
        }

        .btn-info {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .btn-info:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .modal-content {
            background-color: var(--card-background);
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 90%;
            max-width: 1200px;
            position: relative;
            transform: translateY(-30px);
            opacity: 0;
            animation: modalFadeIn 0.4s forwards ease-out;
            border: 1px solid var(--accent-color);
        }

        @keyframes modalFadeIn {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--primary-color);
            font-size: 2em;
            font-weight: 600;
        }

        .close-button {
            color: #aaa;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease, transform 0.2s ease;
        }

        .close-button:hover,
        .close-button:focus {
            color: var(--primary-color);
            transform: rotate(90deg);
            text-decoration: none;
        }

        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 15px;
        }

        .client-info {
            background-color: #fef8f8;
            border: 1px solid var(--accent-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: inset 0 1px 5px rgba(0,0,0,0.03);
        }

        .client-info p {
            margin: 8px 0;
            font-size: 1.05em;
        }

        .commande-block {
            background-color: var(--card-background);
            border: 1px solid var(--accent-color);
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.07);
            position: relative;
        }

        .commande-block h4 {
            color: var(--primary-color);
            font-size: 1.6em;
            margin: 0 0 15px 0;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            font-weight: 600;
        }

        .commande-block p {
            margin: 6px 0;
            font-size: 1em;
            color: var(--light-text-color);
        }

        .commande-block strong {
            color: var(--text-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: var(--card-background);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            border: 1px solid var(--accent-color);
            text-align: center;
            vertical-align: middle;
            font-size: 0.95em;
        }

        th {
            background-color: var(--primary-color);
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background-color: var(--accent-color);
        }

        .img-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.05);
        }

        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 2px;
        }

        .badge.status-Prête { background-color: var(--warning-color); color: #fff; }
        .badge.status-Livrée { background-color: var(--success-color); color: #fff; }
        .badge.status-En-attente { background-color: var(--info-color); color: #fff; }

        /* New badges for delivery status */
        .badge.delivery-Livrée { background-color: var(--success-color); color: #fff; }
        .badge.delivery-Non-livrée { background-color: var(--danger-color); color: #fff; }
        .badge.delivery-En-cours-de-livraison { background-color: var(--in-progress-color); color: var(--text-color); }
        .badge.delivery-Non-spécifié { background-color: gray; color: white; }


        .total-line {
            text-align: right;
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.2em;
            margin-top: 15px;
            padding-right: 10px;
            border-top: 1px dashed var(--accent-color);
            padding-top: 10px;
        }

        .grand-total {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 30px;
            font-size: 1.3em;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .main-content {
            transition: margin-left 0.3s ease;
            margin-left: 0;
        }

        #check:checked ~ .main-content {
            margin-left: 250px;
        }

        .delivery-actions {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }

        .delivery-actions button {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .delivery-actions button.btn-delivered {
            background-color: var(--success-color);
            color: white;
        }

        .delivery-actions button.btn-delivered:hover {
            background-color: #4CAF50;
        }

        .delivery-actions button.btn-not-delivered {
            background-color: var(--danger-color);
            color: white;
        }
        .delivery-actions button.btn-not-delivered:hover {
            background-color: #CC0000;
        }

        .delivery-actions button.btn-in-progress {
            background-color: var(--in-progress-color);
            color: var(--text-color);
        }
        .delivery-actions button.btn-in-progress:hover {
            background-color: #e0a800;
        }

        .delivery-actions input[type="date"] {
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 1em;
            flex-grow: 1; /* Allow the date input to take available space */
            min-width: 150px; /* Ensure it's not too small */
        }
        .delivery-actions .date-submit-button {
            background-color: var(--info-color);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        .delivery-actions .date-submit-button:hover {
            background-color: #409ad1;
        }


        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            h1 {
                font-size: 2.2em;
                margin: 30px 0;
            }
            
            .clients-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .client-card {
                padding: 20px;
            }
            
            .modal-content {
                padding: 20px;
                width: 95%;
            }
            
            .modal-header h3 {
                font-size: 1.6em;
            }
            
            table {
                font-size: 0.85em;
            }
            
            th, td {
                padding: 8px;
            }
            .delivery-actions {
                flex-direction: column; /* Stack buttons and date picker vertically */
                align-items: stretch; /* Stretch items to full width */
            }
            .delivery-actions button, .delivery-actions input[type="date"] {
                width: 100%; /* Make them full width */
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.8em;
            }
            
            .client-card {
                padding: 15px;
            }
            
            .modal-content {
                padding: 15px;
            }
            
            .modal-header h3 {
                font-size: 1.4em;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                font-size: 0.8em;
            }
        }
    </style>
</head>
<body>

<?php include 'catmenul.php'; ?>

<div class="main-content">
    <h1>Commandes prêtes à livrer</h1>

    <?php if (empty($clients)): ?>
        <p style="text-align:center; color:gray; font-size: 1.2em;">Aucune commande prête pour le moment.</p>
    <?php else: ?>
        <div class="clients-grid">
            <?php foreach ($clients as $client): ?>
                <div class="client-card">
                    <h3><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></h3>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($client['telephone']) ?></p>
                    <p><strong>Ville :</strong> <?= htmlspecialchars($client['ville']) ?></p>
                    <p><strong>Adresse :</strong> <?= htmlspecialchars($client['lieu_habitation']) ?></p>
                    
                    <button class="btn-info" data-id-client="<?= htmlspecialchars($client['id_client']) ?>">
                        Plus d'infos
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="clientOrdersModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Commandes de <span id="client-name-modal"></span></h3>
            <span class="close-button">&times;</span>
        </div>
        <div class="modal-body" id="client-orders-container">
            <p>Chargement des commandes...</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('clientOrdersModal');
    const closeButton = document.querySelector('.close-button');
    const clientNameSpan = document.getElementById('client-name-modal');
    const ordersContainer = document.getElementById('client-orders-container');
    const btnInfoButtons = document.querySelectorAll('.btn-info');

    // Function to render orders
    function renderOrders(data) {
        let htmlContent = `
            <div class="client-info">
                <p><strong>Client :</strong> ${data.client_info.prenom} ${data.client_info.nom}</p>
                <p><strong>Téléphone :</strong> ${data.client_info.telephone}</p>
                <p><strong>Pays :</strong> ${data.client_info.pays}</p>
                <p><strong>Ville :</strong> ${data.client_info.ville}</p>
                <p><strong>Adresse :</strong> ${data.client_info.lieu_habitation}</p>
            </div>
        `;

        let grandTotal = 0;

        Object.values(data.commandes).forEach(commande => {
            let totalCommande = 0;
            const deliveryStatus = commande.commande_details.statut || 'Non spécifié';

            htmlContent += `
                <div class="commande-block">
                    <h4>Commande #${commande.commande_details.id_commande} - ${commande.commande_details.date_commande}</h4>
                    <p><strong>Mode de paiement :</strong> ${commande.commande_details.mode_paiement}</p>
                    <p><strong>Statut livraison:</strong> <span class="badge delivery-${deliveryStatus.replace(/ /g, '-')}">${deliveryStatus}</span></p>
                    <p><strong>Date de livraison souhaitée :</strong> ${commande.commande_details.date_livraison || 'Non spécifiée'}</p>

                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Modèle</th>
                                <th>Tissu</th>
                                <th>Quantité</th>
                                <th>Taille</th>
                                <th>Personnalisation</th>
                                <th>Prix unitaire</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            commande.articles.forEach(article => {
                const sousTotal = article.quantite * article.prix;
                totalCommande += sousTotal;
                const tissuImg = article.tissu ? article.tissu : 'aucun.png';
                
                htmlContent += `
                            <tr>
                                <td><img src="uploads/${article.image}" class="img-thumbnail" alt="Modèle"></td>
                                <td>${article.nom_modele}</td>
                                <td><img src="uploads/${tissuImg}" class="img-thumbnail" alt="Tissu"></td>
                                <td>${parseInt(article.quantite)}</td>
                                <td>${article.taille_standard}</td>
                                <td>${article.description_modele ? article.description_modele : 'Aucune'}</td>
                                <td>${new Intl.NumberFormat('fr-FR').format(article.prix)} FCFA</td>
                                <td>${new Intl.NumberFormat('fr-FR').format(sousTotal)} FCFA</td>
                            </tr>
                `;
            });

            htmlContent += `
                        </tbody>
                    </table>
                    <p class="total-line">Total commande : ${new Intl.NumberFormat('fr-FR').format(totalCommande)} FCFA</p>

                    <div class="delivery-actions">
                        <button class="btn-delivered" data-id-commande="${commande.commande_details.id_commande}" data-status="Livrée">Livrée</button>
                        <button class="btn-not-delivered" data-id-commande="${commande.commande_details.id_commande}" data-status="Non livrée">Non livrée</button>
                        <button class="btn-in-progress" data-id-commande="${commande.commande_details.id_commande}" data-status="En cours de livraison">En cours de livraison</button>
                        
                    </div>
                </div>
            `;

            grandTotal += totalCommande;
        });

        htmlContent += `
            <div class="grand-total">
                <strong>Total général : ${new Intl.NumberFormat('fr-FR').format(grandTotal)} FCFA</strong>
            </div>
        `;

        ordersContainer.innerHTML = htmlContent;

        // Add event listeners to newly created buttons
        ordersContainer.querySelectorAll('.btn-delivered, .btn-not-delivered, .btn-in-progress').forEach(button => {
            button.addEventListener('click', function() {
                const idCommande = this.dataset.idCommande;
                const status = this.dataset.status;
                window.location.href = `valider_livraison.php?id_commande=${idCommande}&statut=${status}`;
            });
        });
    }

    // Ouvrir la modal
    btnInfoButtons.forEach(button => {
        button.addEventListener('click', function() {
            const idClient = this.dataset.idClient;
            const clientName = this.parentNode.querySelector('h3').textContent;
            
            clientNameSpan.textContent = clientName;
            ordersContainer.innerHTML = '<p>Chargement des commandes...</p>';
            modal.style.display = 'flex';

            // Récupérer les données via AJAX
            fetch(`dashboard_livreur.php?id_client=${idClient}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        ordersContainer.innerHTML = `<p style="color: red;">${data.error}</p>`;
                    } else if (Object.keys(data.commandes).length === 0) {
                        ordersContainer.innerHTML = '<p>Aucune commande trouvée pour ce client.</p>';
                    } else {
                        renderOrders(data);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    ordersContainer.innerHTML = '<p style="color: red;">Erreur lors du chargement des données.</p>';
                });
        });
    });

    // Fermer la modal
    closeButton.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Fermer la modal en cliquant en dehors
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});
</script>

</body>
</html>