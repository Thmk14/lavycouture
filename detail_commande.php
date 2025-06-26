<?php
require 'config.php';
require 'session.php';

// Vérifier si la session est déjà démarrée
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$id_client = $_SESSION['id'] ?? null;

if (!$id_client) {
    die("Erreur : ID client non défini.");
}

// Suppression de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_commande_id'])) {
    $commande_id = $_POST['delete_commande_id'];

    // Vérification du statut de la commande
    $stmt_status = $pdo->prepare("SELECT statut FROM commande WHERE id_commande = ?");
    $stmt_status->execute([$commande_id]);
    $status = $stmt_status->fetch(PDO::FETCH_ASSOC);

    if ($status && $status['statut'] === 'E') {
        try {
            $pdo->beginTransaction();

            // Suppression des articles liés à la commande
            $stmt = $pdo->prepare("DELETE FROM concerner WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            // Suppression de la commande
            $stmt = $pdo->prepare("DELETE FROM commande WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            $pdo->commit();
            header("Location: detail_commande.php?deleted=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erreur lors de la suppression : " . $e->getMessage());
        }
    } else {
        $_SESSION['message_error'] = "Impossible de supprimer cette commande (statut non autorisé).";
        echo "<script>alert('Impossible de supprimer cette commande (statut non autorisé).'); window.location.href='detail_commande.php';</script>";
        exit();
    }
}

// Récupération des données du client et des commandes
$sql = "SELECT *
        FROM concerner c
        JOIN article art ON c.id_article = art.id_article
        JOIN commande cmd ON c.id_commande = cmd.id_commande
        JOIN mensuration m ON cmd.id_mensuration = m.id_mensuration
        WHERE cmd.id_client = ? AND cmd.statut != ? AND cmd.statut != ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_client,'En attente','Livrée']);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <title>Mes Commandes - Lavy Couture</title>
    <style>
        :root {
            --primary-color:rgb(219, 46, 176);
            --secondary-color:rgb(167, 40, 135);
            --accent-color: #f3c5dd;
            --text-color: #333;
            --light-text-color: #555;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --border-color: #eee;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --danger-color: #dc3545;
            --gradient-primary: linear-gradient(135deg,rgb(219, 46, 182) 0%,rgb(167, 40, 131) 100%);
            --gradient-secondary: linear-gradient(135deg,rgb(243, 197, 229) 0%,rgb(232, 179, 214) 100%);
            --shadow-light: 0 2px 10px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 5px 20px rgba(0, 0, 0, 0.12);
            --shadow-heavy: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            padding-top: 120px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .page-header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 70px 0px 50px 0px ;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        .stats-card {
            background: var(--card-background);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-color);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 900;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .stats-label {
            font-size: 1.2rem;
            color: var(--light-text-color);
            font-weight: 500;
        }

        .history-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
            margin-bottom: 30px;
        }

        .history-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
            color: white;
            text-decoration: none;
        }

        .history-btn i {
            font-size: 1.2rem;
        }

        .commande-block {
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: var(--shadow-medium);
            margin-bottom: 30px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
        }

        .commande-block:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-heavy);
        }

        .commande-header {
            background: var(--gradient-secondary);
            padding: 25px 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .commande-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .commande-title i {
            font-size: 1.5rem;
        }

        .commande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            color: var(--primary-color);
            font-size: 1.2rem;
            width: 20px;
        }

        .info-item strong {
            color: var(--text-color);
            font-weight: 600;
        }

        .info-item span {
            color: var(--light-text-color);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .status-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .status-En-attente { 
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        .status-Validée { 
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .status-En-préparation { 
            background: linear-gradient(135deg, #cce7ff 0%, #b3d9ff 100%); 
            color: #004085; 
            border: 1px solid #b3d9ff;
        }
        .status-Prête { 
            background: linear-gradient(135deg, #ffeaa7 0%, #fdcb6e 100%); 
            color: #6c5ce7; 
            border: 1px solid #fdcb6e;
        }
        .status-Livrée { 
            background: linear-gradient(135deg, #d1f2eb 0%, #b8e6e6 100%); 
            color: #0c5460; 
            border: 1px solid #b8e6e6;
        }
        .status-Annulée { 
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%); 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }

        .commande-content {
            padding: 30px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-background);
            border-radius: 15px;
            overflow: hidden;
        }

        th {
            background: var(--gradient-primary);
            color: white;
            padding: 20px 15px;
            text-align: center;
            font-weight: 600;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
        }

        th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255,255,255,0.3);
        }

        td {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-image:hover {
            transform: scale(1.15) rotate(2deg);
            box-shadow: 0 8px 25px rgba(219, 46, 139, 0.3);
        }

        .product-name {
            font-weight: 600;
            color: var(--text-color);
        }

        .product-details {
            color: var(--light-text-color);
            font-size: 0.9rem;
        }

        .price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
            position: relative;
            padding: 5px 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(219, 46, 139, 0.1) 0%, rgba(167, 40, 114, 0.1) 100%);
        }

        .total-amount {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--secondary-color);
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .total-label {
            font-size: 1.2rem;
            color: var(--light-text-color);
            font-weight: 500;
        }

        .no-orders {
            text-align: center;
            padding: 80px 20px;
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: var(--shadow-medium);
            margin: 50px 0;
            position: relative;
            overflow: hidden;
        }

        .no-orders::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .no-orders i {
            font-size: 4rem;
            color: var(--accent-color);
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        .no-orders h3 {
            font-size: 2rem;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .no-orders p {
            font-size: 1.1rem;
            color: var(--light-text-color);
            margin-bottom: 30px;
        }

        .cta-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            padding: 18px 35px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: var(--shadow-heavy);
            color: white;
            text-decoration: none;
        }

        .cta-button i {
            font-size: 1.3rem;
        }

        .grand-total {
            background: var(--gradient-primary);
            color: white;
            text-align: center;
            padding: 30px;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            margin: 50px 0;
            position: relative;
            overflow: hidden;
        }

        .grand-total::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .grand-total h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .grand-total .amount {
            font-size: 3.5rem;
            font-weight: 900;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            margin: 5% auto;
            display: block;
            max-width: 90%;
            width: 800px;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            animation: slideInUp 0.4s ease-out;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            z-index: 1001;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close:hover {
            color: var(--primary-color);
            transform: rotate(90deg) scale(1.1);
            background: rgba(255,255,255,0.9);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
                padding-top: 100px;
            }

            .page-header h1 {
                font-size: 2.5rem;
            }

            .commande-header {
                padding: 20px;
            }

            .commande-title {
                font-size: 1.5rem;
            }

            .commande-info {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .commande-content {
                padding: 20px;
            }

            th, td {
                padding: 12px 8px;
                font-size: 0.85rem;
            }

            .product-image {
                width: 60px;
                height: 60px;
            }

            .total-amount {
                font-size: 2rem;
            }

            .grand-total .amount {
                font-size: 2.5rem;
            }

            .stats-number {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .commande-block {
                margin-bottom: 20px;
            }

            .commande-header {
                padding: 15px;
            }

            .commande-content {
                padding: 15px;
            }

            table {
                font-size: 0.8rem;
            }

            th, td {
                padding: 8px 5px;
            }

            .product-image {
                width: 50px;
                height: 50px;
            }

            .cta-button {
                padding: 15px 25px;
                font-size: 1rem;
            }
        }

        /* Animation for status badges */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        @keyframes slideInUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0,0,0);
            }
            40%, 43% {
                transform: translate3d(0, -8px, 0);
            }
            70% {
                transform: translate3d(0, -4px, 0);
            }
            90% {
                transform: translate3d(0, -2px, 0);
            }
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .status-badge {
            animation: pulse 2s infinite;
        }

        .commande-block {
            animation: slideInUp 0.6s ease-out;
        }

        .stats-card {
            animation: bounce 1s ease-out;
        }

        .page-header h1 {
            animation: fadeIn 1s ease-out;
        }

        /* Enhanced hover effects */
        .commande-block:hover .commande-title {
            transform: scale(1.02);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.15) rotate(2deg);
            box-shadow: 0 8px 25px rgba(219, 46, 139, 0.3);
        }

        /* Enhanced table styling */
        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
        }

        /* Enhanced price styling */
        .price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
            position: relative;
            padding: 5px 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, rgba(219, 46, 139, 0.1) 0%, rgba(167, 40, 114, 0.1) 100%);
        }

        /* Enhanced grand total */
        .grand-total {
            background: var(--gradient-primary);
            color: white;
            text-align: center;
            padding: 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-heavy);
            margin: 50px 0;
            position: relative;
            overflow: hidden;
        }

        .grand-total::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        .grand-total h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }

        .grand-total .amount {
            font-size: 3.5rem;
            font-weight: 900;
            position: relative;
            z-index: 1;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        /* Enhanced no orders section */
        .no-orders i {
            animation: bounce 2s infinite;
        }

        /* Enhanced CTA button */
        .cta-button {
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px) scale(1.05);
        }

        /* Enhanced modal */
        .modal {
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            animation: slideInUp 0.4s ease-out;
        }

        .close {
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close:hover {
            transform: rotate(90deg) scale(1.1);
            background: rgba(255,255,255,0.9);
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>

<div class="main-content">
    <div class="page-header">
        <h1><i class="fas fa-shopping-bag"></i> Mes Commandes</h1>
    </div>

    <div style="text-align: center; margin-bottom: 30px;">
        <a href="historique" class="history-btn">
            <i class="fas fa-history"></i>
            Voir l'historique
        </a>
    </div>

    <?php if (count($commandes) > 0): ?>
        <div class="stats-card">
            <div class="stats-number"><?= count($commandes) ?></div>
            <div class="stats-label">Commandes en cours</div>
        </div>

        <?php 
        $total_general = 0;
        foreach ($commandes as $id => $data): 
            $sous_total = $data['quantite'] * $data['prix'];
            $total_general += $sous_total;
        ?>
            <div class="commande-block">
                <div class="commande-header">
                    <div class="commande-title">
                        <i class="fas fa-receipt"></i>
                        Commande #<?= htmlspecialchars($data['id_commande']) ?>
                    </div>
                    <div class="commande-info">
                        <div class="info-item">
                            <i class="fas fa-credit-card"></i>
                            <strong>Paiement:</strong> <span><?= htmlspecialchars($data['mode_paiement']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <strong>Date:</strong> <span><?= htmlspecialchars($data['date_commande']) ?></span>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-info-circle"></i>
                            <strong>Statut:</strong> 
                            <span class="status-badge status-<?= str_replace(' ', '-', htmlspecialchars($data['statut'])) ?>">
                                <?= htmlspecialchars($data['statut']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="commande-content">
                    <div class="table-container">
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
                                <?php 
                                $tissu = !empty($data['tissu']) ? $data['tissu'] : 'aucun.png';
                                ?>
                                <tr>
                                    <td>
                                        <img src="uploads/<?= htmlspecialchars($data['image']) ?>" 
                                             class="product-image" 
                                             alt="<?= htmlspecialchars($data['nom_modele']) ?>">
                                    </td>
                                    <td>
                                        <div class="product-name"><?= htmlspecialchars($data['nom_modele']) ?></div>
                                    </td>
                                    <td>
                                        <img src="uploads/<?= $tissu ?>" 
                                             class="product-image" 
                                             alt="Tissu">
                                    </td>
                                    <td>
                                        <div class="product-details"><?= htmlspecialchars($data['quantite']) ?></div>
                                    </td>
                                    <td>
                                        <div class="product-details"><?= htmlspecialchars($data['taille_standard']) ?></div>
                                    </td>
                                    <td>
                                        <div class="product-details">
                                            <?= !empty($data['description_modele']) ? htmlspecialchars($data['description_modele']) : "Aucune" ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="price"><?= number_format($data['prix'], 0, ',', ' ') ?> FCFA</div>
                                    </td>
                                    <td>
                                        <div class="price"><?= number_format($sous_total, 0, ',', ' ') ?> FCFA</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

               
            </div>
        <?php endforeach; ?>

        <div class="grand-total">
            <h2><i class="fas fa-calculator"></i> Montant Total Général</h2>
            <div class="amount"><?= number_format($total_general, 0, ',', ' ') ?> FCFA</div>
        </div>

    <?php else: ?>
        <div class="no-orders">
            <i class="fas fa-shopping-cart"></i>
            <h3>Aucune commande trouvée</h3>
            <p>Vous n'avez pas encore de commandes en cours. Découvrez notre collection et passez votre première commande !</p>
            <a href="catalogue.php" class="cta-button">
                <i class="fas fa-plus"></i>
                Passer une commande
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Modal pour les images -->
<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
</div>

<script>
// Modal functionality
var modal = document.getElementById("myModal");
var modalImg = document.getElementById("img01");

document.querySelectorAll(".product-image").forEach(function(img) {
    img.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.src;
    };
});

var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
    modal.style.display = "none";
};

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};

// Smooth scroll animation
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Add loading animation to images
document.querySelectorAll('.product-image').forEach(img => {
    img.addEventListener('load', function() {
        this.classList.remove('loading');
    });
    img.classList.add('loading');
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>



