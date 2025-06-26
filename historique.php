<?php
require('config.php');
require('session.php');

// Vérifier la connexion
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$id_client = $_SESSION['id'];

// Préparer la requête
$sql = $pdo->prepare("SELECT * FROM concerner
    JOIN commande ON concerner.id_commande = commande.id_commande
    JOIN article ON concerner.id_article = article.id_article
    JOIN client ON commande.id_client = client.id_client
    WHERE client.id_client = ? AND commande.statut = 'Livrée'
    ORDER BY commande.date_commande DESC");

$sql->execute([$id_client]);
$commandes = $sql->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <title>Historique des Commandes - Lavy Couture</title>
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
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --danger-color: #dc3545;
            --gradient-primary: linear-gradient(135deg, #db2e8b 0%, #a72872 100%);
            --gradient-secondary: linear-gradient(135deg, #f3c5dd 0%, #e8b3d0 100%);
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
            padding-top: 40px;
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
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
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            animation: fadeIn 1s ease-out;
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
            animation: bounce 1s ease-out;
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

        .commandes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .commande {
            background: var(--card-background);
            border-radius: 20px;
            box-shadow: var(--shadow-medium);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            position: relative;
            animation: slideInUp 0.6s ease-out;
        }

        .commande:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-heavy);
        }

        .commande-header {
            background: var(--gradient-secondary);
            padding: 25px 30px;
            border-bottom: 1px solid var(--border-color);
        }

        .commande-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .commande-title i {
            font-size: 1.5rem;
        }

        .commande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-item i {
            color: var(--primary-color);
            font-size: 1rem;
            width: 16px;
        }

        .info-item strong {
            color: var(--text-color);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .info-item span {
            color: var(--light-text-color);
            font-size: 0.9rem;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #d1f2eb 0%, #b8e6e6 100%);
            color: #0c5460;
            border: 1px solid #b8e6e6;
            animation: pulse 2s infinite;
        }

        .commande-content {
            padding: 25px 30px;
        }

        .commande-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-radius: 15px;
            background: linear-gradient(135deg, #fef8f8 0%, #f8f9fa 100%);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .commande-item:hover {
            transform: scale(1.02);
            box-shadow: var(--shadow-light);
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 12px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-image:hover {
            transform: scale(1.1);
            border-color: var(--primary-color);
            box-shadow: var(--shadow-medium);
        }

        .product-details {
            flex: 1;
            min-width: 0;
        }

        .product-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .product-info {
            margin: 8px 0;
            font-size: 0.95rem;
            color: var(--light-text-color);
            line-height: 1.5;
        }

        .product-info strong {
            color: var(--primary-color);
            font-weight: 600;
        }

        .price-breakdown {
            background: linear-gradient(135deg, rgba(219, 46, 139, 0.1) 0%, rgba(167, 40, 114, 0.1) 100%);
            padding: 12px 16px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 4px solid var(--primary-color);
        }

        .total-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .tissu-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .tissu-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px dashed var(--border-color);
            transition: all 0.3s ease;
        }

        .tissu-img:hover {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .commande-footer {
            padding: 20px 30px;
            background: var(--gradient-secondary);
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gradient-primary);
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
            border: none;
            cursor: pointer;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-heavy);
            color: white;
            text-decoration: none;
        }

        .btn-danger i {
            font-size: 1rem;
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

        .back-btn {
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

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-heavy);
            color: white;
            text-decoration: none;
        }

        .back-btn i {
            font-size: 1.2rem;
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

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 15px;
            }

            .page-header h1 {
                font-size: 2.5rem;
            }

            .commandes-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .commande-header {
                padding: 20px;
            }

            .commande-title {
                font-size: 1.5rem;
            }

            .commande-info {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .commande-content {
                padding: 20px;
            }

            .commande-item {
                flex-direction: column;
                text-align: center;
            }

            .product-image {
                width: 80px;
                height: 80px;
                margin: 0 auto;
            }

            .commande-footer {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .stats-number {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .commande {
                margin: 0 10px;
            }

            .commande-header {
                padding: 15px;
            }

            .commande-content {
                padding: 15px;
            }

            .product-name {
                font-size: 1.1rem;
            }

            .btn-danger {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Historique de vos commandes</h1>
        </div>

        <div style="text-align: center; margin-bottom: 30px;">
            <a href="detail_commande.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Retour aux commandes
            </a>
        </div>

        <?php if (count($commandes) > 0): ?>
            <div class="stats-card">
                <div class="stats-number"><?= count($commandes) ?></div>
                <div class="stats-label">Commandes livrées</div>
            </div>

            <div class="commandes-grid">
                <?php foreach ($commandes as $commande): ?>
                    <div class="commande">
                        <div class="commande-header">
                            <div class="commande-title">
                                <i class="fas fa-check-circle"></i>
                                Commande #<?= ($commande['id_commande']) ?>
                            </div>
                            <div class="commande-info">
                                <div class="info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <strong>Commande:</strong> <span><?= ($commande['date_commande']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-truck"></i>
                                    <strong>Livraison:</strong> <span><?= ($commande['date_livraison']) ?></span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Statut:</strong> 
                                    <span class="status-badge">
                                        <i class="fas fa-check"></i>
                                        <?=($commande['statut']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="commande-content">
                            <?php
                            // Calcul du prix total par article
                            $prix_unitaire = (float)$commande['prix'];
                            $supplement = (float)$commande['supplement_prix'];
                            $quantite = (int)$commande['quantite'];
                            $total = ($prix_unitaire + $supplement) * $quantite;
                            ?>

                            <div class="commande-item">
                                <img src="uploads/<?= htmlspecialchars($commande['image']) ?>" 
                                     alt="<?= htmlspecialchars($commande['nom_modele']) ?>" 
                                     class="product-image">

                                <div class="product-details">
                                    <div class="product-name"><?= htmlspecialchars($commande['nom_modele']) ?></div>
                                    
                                    <div class="price-breakdown">
                                        <div class="product-info">
                                            <?= number_format($prix_unitaire, 0, ',', ' ') ?> FCFA × <?= $quantite ?>
                                            <?php if ($supplement > 0): ?>
                                                <br>+ <?= number_format($supplement, 0, ',', ' ') ?> FCFA supplément
                                            <?php endif; ?>
                                        </div>
                                        <div class="total-price">
                                            Total: <?= number_format($total, 0, ',', ' ') ?> FCFA
                                        </div>
                                    </div>

                                    <?php if (!empty($commande['description_modele'])): ?>
                                        <div class="product-info">
                                            <strong>Description:</strong> <?= htmlspecialchars($commande['description_modele']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($commande['tissu'])): ?>
                                        <div class="tissu-section">
                                            <span class="product-info"><strong>Tissu:</strong></span>
                                            <img src="uploads/<?= htmlspecialchars($commande['tissu']) ?>" 
                                                 alt="Tissu" 
                                                 class="tissu-img">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="commande-footer">
                            <a class="btn-danger" 
                               href="supprimer_commande.php?id=<?= urlencode($commande['id_commande']) ?>" 
                               onclick="return confirm('Confirmer la suppression de la commande ?')">
                                <i class="fas fa-trash"></i>
                                Supprimer la commande
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-box-open"></i>
                <h3>Aucune commande livrée</h3>
                <p>Vous n'avez pas encore de commandes livrées dans votre historique.</p>
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

        document.querySelectorAll(".product-image, .tissu-img").forEach(function(img) {
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
        document.querySelectorAll('.product-image, .tissu-img').forEach(img => {
            img.addEventListener('load', function() {
                this.classList.remove('loading');
            });
            img.classList.add('loading');
        });
    </script>
</body>
</html>
