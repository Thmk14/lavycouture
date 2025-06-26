<?php
require 'config.php';
require 'session.php';

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

        // Récupérer les commandes du client avec tous les détails
    $sql = "SELECT c.*, art.*, cmd.*, m.*
            FROM concerner c
            JOIN article art ON c.id_article = art.id_article
            JOIN commande cmd ON c.id_commande = cmd.id_commande
            JOIN mensuration m ON cmd.id_mensuration = m.id_mensuration
                WHERE cmd.id_client = ? AND cmd.etat_commande = 1
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

// Récupérer tous les clients qui ont des commandes (etat_commande = 1)
try {
    $stmt_clients = $pdo->prepare("
        SELECT DISTINCT c.id_client, c.nom, c.prenom, c.telephone, c.pays, c.ville, c.lieu_habitation
        FROM client c
        JOIN commande cmd ON c.id_client = cmd.id_client
        WHERE cmd.etat_commande = 1 AND c.email IS NOT NULL
        ORDER BY c.nom, c.prenom
    ");
    $stmt_clients->execute();
    $clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Clients et Commandes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #db2e8b;
            --secondary-color: #a72872;
            --accent-color: #f3c5dd;
            --text-color: #333;
            --light-text-color: #555;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #eee;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 80px auto 40px auto;
            padding: 30px;
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 40px;
            font-size: 3em;
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }

        .client-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .client-card {
            background-color: var(--card-background);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            border-left: 6px solid var(--secondary-color);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .client-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .client-card p {
            margin: 8px 0;
            font-size: 1.05em;
            color: var(--light-text-color);
        }

        .client-card strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .client-card .btn-info {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 20px;
            align-self: flex-start; /* Align button to the left/bottom */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .client-card .btn-info:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Commande Block styles */
        .commande-block {
            background-color: var(--card-background);
            border: 1px solid var(--accent-color);
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.07);
            position: relative;
        }

        .commande-block h2 {
            color: var(--secondary-color);
            font-size: 2em;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 15px;
            font-weight: 600;
        }

        .commande-block p {
            margin: 8px 0;
            font-size: 1.05em;
            color: var(--light-text-color);
        }

        .commande-block strong {
            color: var(--text-color);
        }

        /* Modifier Button */
        .btn-modifier {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.95em;
            font-weight: 500;
            transition: background-color 0.3s ease, transform 0.2s ease;
            position: absolute;
            top: 25px;
            right: 25px;
            z-index: 10;
        }

        .btn-modifier:hover {
            background-color: #882162;
            transform: translateY(-2px);
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background-color: var(--card-background);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 15px;
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
            background-color: #fdf6fa;
        }

        /* Images */
        .img-thumbnail {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .img-thumbnail:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 7px 14px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 2px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .badge.paid {
            background-color: var(--success-color);
            color: #fff;
        }

        .badge.unpaid {
            background-color: var(--error-color);
            color: #fff;
        }

        .badge.status-En-attente { background-color: #ffc107; color: #343a40; }
        .badge.status-Validée { background-color: var(--success-color); color: #fff; }
        .badge.status-Annulée { background-color: var(--error-color); color: #fff; }
        .badge.status-En-preparation { background-color: #17a2b8; color: #fff; }
        .badge.status-Expédiée { background-color: #6f42c1; color: #fff; }
        .badge.status-Livrée { background-color: #007bff; color: #fff; }
        .badge.status-Prête { background-color: #ff8c00; color: #fff; }

        /* Total Line */
        .total-line {
            text-align: right;
            font-weight: bold;
            color: var(--secondary-color);
            font-size: 1.3em;
            margin-top: 20px;
            padding-right: 10px;
            border-top: 1px dashed var(--accent-color);
            padding-top: 15px;
        }

        /* Global Modal Styles */
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
            padding: 20px; /* Add some padding for smaller screens */
        }

        .modal-content {
            background-color: var(--card-background);
            margin: auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            width: 90%;
            max-width: 500px;
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
            color: var(--secondary-color);
            transform: rotate(90deg);
            text-decoration: none;
        }

        .modal-body label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--light-text-color);
            font-size: 1em;
        }

        .modal-body select {
            width: 100%;
            padding: 12px;
            margin-bottom: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            font-size: 1em;
            background-color: var(--background-color);
            appearance: none; /* Remove default select styling */
            -webkit-appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg fill="%23db2e8b" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/><path d="M0 0h24v24H0z" fill="none"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 24px;
        }

        .modal-body select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(219, 46, 139, 0.2);
        }

        .modal-footer {
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            text-align: right;
            margin-top: 20px;
        }

        .modal-footer button {
            background-color: var(--success-color);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.05em;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .modal-footer button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .message-box {
            padding: 12px;
            margin-top: 15px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            display: none;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        /* Specific styles for Client Orders Modal */
        #clientOrdersModal .modal-content {
            max-width: 1200px;
        }
        #clientOrdersModal .modal-body {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 15px; /* For scrollbar space */
        }
        #clientOrdersModal .client-info {
            background-color: #fef8f8;
            border: 1px solid var(--accent-color);
            border-radius: 8px;
            padding: 15px 30px;
            margin-bottom: 30px;
            box-shadow: inset 0 1px 5px rgba(0,0,0,0.03);
        }
        #clientOrdersModal .client-info p {
            margin: 5px 0;
            font-size: 1em;
        }

        /* Body blur effect when modal is open */
        body.modal-open > .container,
        body.modal-open > .catmenu {
            filter: blur(5px) brightness(0.7);
            transition: filter 0.3s ease-out;
            pointer-events: none;
        }
        body:not(.modal-open) > .container,
        body:not(.modal-open) > .catmenu {
            filter: none;
            transition: filter 0.3s ease-out;
            pointer-events: auto;
        }


        /* Responsive */
        @media (max-width: 992px) {
            .container {
                margin: 60px auto 30px auto;
                padding: 25px;
            }
            h1 {
                font-size: 2.5em;
                margin-bottom: 35px;
            }
            .client-card, .commande-block {
                padding: 20px;
            }
            .commande-block h2 {
                font-size: 1.8em;
            }
            .btn-modifier {
                top: 20px;
                right: 20px;
                padding: 8px 15px;
                font-size: 0.9em;
            }
            th, td {
                padding: 12px;
                font-size: 0.9em;
            }
            .img-thumbnail {
                width: 60px;
                height: 60px;
            }
            .total-line {
                font-size: 1.2em;
            }
            .modal-content {
                width: 95%;
                padding: 25px;
            }
            .modal-header h3 {
                font-size: 1.8em;
            }
            .close-button {
                font-size: 35px;
            }
        }

        @media (max-width: 768px) {
            .container {
                margin: 50px auto 20px auto;
                padding: 15px;
            }
            h1 {
                font-size: 2.2em;
                margin-bottom: 30px;
            }
            .client-list {
                grid-template-columns: 1fr;
            }
            .client-card p {
                font-size: 1em;
            }
            .commande-block {
                padding: 15px;
                margin-bottom: 25px;
            }
            .commande-block h2 {
                font-size: 1.6em;
            }
            .btn-modifier {
                position: static;
                display: block;
                width: fit-content;
                margin-top: 15px;
                margin-left: auto;
                margin-right: auto;
            }
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
                font-size: 0.8em;
            }
            th, td {
                padding: 10px;
            }
            .img-thumbnail {
                width: 50px;
                height: 50px;
            }
            .badge {
                padding: 5px 10px;
                font-size: 0.75em;
            }
            .total-line {
                font-size: 1.1em;
                text-align: center;
            }
            .modal-content {
                padding: 20px;
            }
            .modal-header h3 {
                font-size: 1.6em;
            }
            .close-button {
                font-size: 30px;
            }
            .modal-body select {
                padding: 10px;
            }
            .modal-footer button {
                padding: 10px 20px;
                font-size: 1em;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 10px;
            }
            h1 {
                font-size: 1.8em;
                margin-bottom: 20px;
            }
            .client-card {
                padding: 15px;
            }
            .client-card .btn-info {
                padding: 10px 20px;
                font-size: 0.9em;
            }
            .commande-block h2 {
                font-size: 1.4em;
            }
            th, td {
                padding: 8px;
            }
            .modal-header h3 {
                font-size: 1.4em;
            }
            .close-button {
                font-size: 25px;
            }
            .modal-footer button {
                width: 100%;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
<?php include 'catmenu.php'; // Ensure your menu is included here ?>

<h1>Liste des clients et leurs commandes</h1>

<div class="container">
    <?php if (empty($clients)): ?>
        <p>Aucun client avec commande trouvée pour l'instant.</p>
    <?php else: ?>
        <div class="client-list">
            <?php foreach ($clients as $client): ?>
                <div class="client-card">
                    <p><strong>Client :</strong> <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?></p>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($client['telephone']) ?></p>

                    <button class="btn-info" data-id-client="<?= htmlspecialchars($client['id_client']) ?>">Voir les commandes</button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="clientOrdersModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Commandes de <span id="client-name-modal"></span></h3>
            <span class="close-button client-modal-close">&times;</span>
        </div>
        <div class="modal-body" id="client-orders-container">
            <p>Chargement des commandes...</p>
        </div>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier la Commande <span id="modal-commande-id"></span></h3>
            <span class="close-button edit-modal-close">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editCommandeForm" action="update_admin_commande.php" method="POST">
                <input type="hidden" id="modal-hidden-commande-id" name="id_commande">

                <label for="modal-statut">Statut de la commande :</label>
                <select id="modal-statut" name="statut">
                    <option value="En attente">En attente</option>
                    <option value="Validée">Validée</option>
                    <option value="En préparation">En préparation</option>
                    <option value="Prête">Prête</option>
                    <option value="Livrée">Livrée</option>
                    <option value="Annulée">Annulée</option>
                </select>

                <label for="modal-avance">Avance payée :</label>
                <select id="modal-avance" name="avance">
                    <option value="1">Payé</option>
                    <option value="0">Non payé</option>
                </select>

                <div id="modal-message" class="message-box"></div>
                <div class="modal-footer">
                    <button type="submit">Sauvegarder les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Elements for Client Orders Modal ---
    const clientOrdersModal = document.getElementById('clientOrdersModal');
    const clientOrdersModalClose = document.querySelector('.client-modal-close');
    const clientNameModalSpan = document.getElementById('client-name-modal');
    const clientOrdersContainer = document.getElementById('client-orders-container');
    const btnInfoClients = document.querySelectorAll('.btn-info');

    // --- Elements for Edit Order Modal ---
    const editModal = document.getElementById('editModal');
    const editModalClose = document.querySelector('.edit-modal-close');
    const modalCommandeIdSpan = document.getElementById('modal-commande-id');
    const modalHiddenCommandeId = document.getElementById('modal-hidden-commande-id');
    const modalStatut = document.getElementById('modal-statut');
    const modalAvance = document.getElementById('modal-avance');
    const editCommandeForm = document.getElementById('editCommandeForm');
    const modalMessage = document.getElementById('modal-message');

    // --- Functions to toggle blur and interaction ---
    function enableBodyBlur() {
        document.body.classList.add('modal-open');
    }

    function disableBodyBlur() {
        document.body.classList.remove('modal-open');
    }

    // --- Client Orders Modal Logic ---
    btnInfoClients.forEach(button => {
        button.addEventListener('click', function() {
            const idClient = this.dataset.idClient;
            const clientName = this.parentNode.querySelector('p:first-child').textContent.replace('Client : ', ''); // Get client name from card
            clientNameModalSpan.textContent = clientName;
            clientOrdersContainer.innerHTML = '<p>Chargement des commandes...</p>'; // Reset content
            enableBodyBlur();
            clientOrdersModal.style.display = 'flex'; // Use flex for centering

            // Fetch client orders via AJAX
            fetch(`liste_admin_commande.php?id_client=${idClient}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        clientOrdersContainer.innerHTML = `<p class="message-box error">${data.error}</p>`;
                    } else if (Object.keys(data.commandes).length === 0) {
                        clientOrdersContainer.innerHTML = `<p>Aucune commande trouvée pour ce client.</p>`;
                    } else {
                        let htmlContent = `
                            <div class="client-info">
                                <p><strong>Email :</strong> ${data.client_info.email}</p>
                                <p><strong>Téléphone :</strong> ${data.client_info.telephone}</p>
                                <p><strong>Pays :</strong> ${data.client_info.pays}</p>
                                <p><strong>Ville :</strong> ${data.client_info.ville}</p>
                                <p><strong>Lieu d'habitation :</strong> ${data.client_info.lieu_habitation}</p>
                            </div>
                        `;
                        Object.values(data.commandes).forEach(commande => {
                            let total_commande = 0;
                            htmlContent += `
                                <div class="commande-block">
                                    <h2>Commande #${commande.commande_details.id_commande} - ${commande.commande_details.date_commande}</h2>
                                    <p><strong>Mode de paiement :</strong> ${commande.commande_details.mode_paiement}</p>
                                    <p><strong>Statut :</strong> <span class="badge status-${commande.commande_details.statut.replace(' ', '-')}">${commande.commande_details.statut}</span></p>
                                    <p><strong>Avance :</strong>
                                        ${commande.commande_details.avance == 1 ? '<span class="badge paid">Payé</span>' : '<span class="badge unpaid">Non payé</span>'}
                                    </p>

                                    <button class="btn-modifier"
                                        data-id-commande="${commande.commande_details.id_commande}"
                                        data-statut="${commande.commande_details.statut}"
                                        data-avance="${commande.commande_details.avance}"> Modifier
                                    </button>

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
                                const sous_total = article.quantite * article.prix;
                                total_commande += sous_total;
                                const tissu_img = article.tissu ? article.tissu : 'aucun.png';
                                htmlContent += `
                                    <tr>
                                        <td><img src="uploads/${article.image}" class="img-thumbnail" alt="Modèle"></td>
                                        <td>${article.nom_modele}</td>
                                        <td><img src="uploads/${tissu_img}" class="img-thumbnail" alt="Tissu"></td>
                                        <td>${parseInt(article.quantite)}</td>
                                        <td>${article.taille_standard}</td>
                                        <td>${article.description_modele ? article.description_modele : 'Aucune'}</td>
                                        <td>${new Intl.NumberFormat('fr-FR').format(article.prix)} FCFA</td>
                                        <td>${new Intl.NumberFormat('fr-FR').format(sous_total)} FCFA</td>
                                    </tr>
                                `;
                            });
                            htmlContent += `
                                        </tbody>
                                    </table>
                                    <p class="total-line">Total commande : ${new Intl.NumberFormat('fr-FR').format(total_commande)} FCFA</p>
                                </div>
                            `;
                        });
                        clientOrdersContainer.innerHTML = htmlContent;

                        // Re-attach event listeners to new .btn-modifier elements
                        clientOrdersContainer.querySelectorAll('.btn-modifier').forEach(button => {
                            button.addEventListener('click', openEditModal);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching client orders:', error);
                    clientOrdersContainer.innerHTML = `<p class="message-box error">Erreur lors du chargement des commandes : ${error.message}</p>`;
                });
        });
    });

    clientOrdersModalClose.addEventListener('click', function() {
        clientOrdersModal.style.display = 'none';
        disableBodyBlur();
    });

    // Close client orders modal if clicked outside
    window.addEventListener('click', function(event) {
        if (event.target == clientOrdersModal) {
            clientOrdersModal.style.display = 'none';
            disableBodyBlur();
        }
    });

    // --- Edit Order Modal Logic (adapted) ---
    function openEditModal() {
            const idCommande = this.dataset.idCommande;
            const statut = this.dataset.statut;
        const avance = this.dataset.avance;

            modalCommandeIdSpan.textContent = idCommande;
            modalHiddenCommandeId.value = idCommande;
            modalStatut.value = statut;
        modalAvance.value = avance;

        enableBodyBlur(); // Ensure blur is active
        editModal.style.display = 'flex';
        modalMessage.style.display = 'none';
        modalMessage.className = 'message-box';
    }

    // Attach event listener to initial .btn-modifier (if any) and newly created ones
    // Initial buttons (if any, though in this new architecture, they are only in the orders modal)
    document.querySelectorAll('.btn-modifier').forEach(button => {
        button.addEventListener('click', openEditModal);
    });

    editModalClose.addEventListener('click', function() {
        editModal.style.display = 'none';
        disableBodyBlur();
    });

    // Close edit modal if clicked outside
    window.addEventListener('click', function(event) {
        if (event.target == editModal) {
            editModal.style.display = 'none';
            disableBodyBlur();
        }
    });

    // Handle form submission for editing orders
    editCommandeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('update_admin_commande.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            modalMessage.style.display = 'block';
            if (data.includes('succès') || data.includes('success')) {
                modalMessage.className = 'message-box success';
                modalMessage.textContent = 'Modifications sauvegardées avec succès !';
                // Optionally refresh the client orders modal
                setTimeout(() => {
                    editModal.style.display = 'none';
                    disableBodyBlur();
                    // Refresh the current client's orders
                    const currentClientButton = document.querySelector('.btn-info[data-id-client]');
                    if (currentClientButton) {
                        currentClientButton.click();
                    }
                }, 2000);
            } else {
                modalMessage.className = 'message-box error';
                modalMessage.textContent = 'Erreur lors de la sauvegarde : ' + data;
            }
        })
        .catch(error => {
            modalMessage.style.display = 'block';
            modalMessage.className = 'message-box error';
            modalMessage.textContent = 'Erreur de connexion : ' + error.message;
        });
    });
});
</script>

</body>
</html>