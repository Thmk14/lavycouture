<?php

require('config.php'); // Connexion à la base de données
require('session.php'); // Gérer la session utilisateur
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

$isLoggedIn = isset($_SESSION['id']);

function hasDeliveredOrders($pdo, $id_client) {
    $sql="SELECT COUNT(*)
        FROM commande 
        WHERE id_client = ? AND statut = 'Livrée'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_client]);
    return $stmt->fetchColumn() > 0;
}

// New function to check for "ready for delivery" orders
function hasOrdersReadyForDelivery($pdo, $id_client) {
    $sql = "SELECT COUNT(*)
            FROM commande 
            WHERE id_client = ? AND statut = 'Prête'"; // Assuming 'prêt à être livré' is your status
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_client]);
    return $stmt->fetchColumn() > 0;
}


function getPanierCount($pdo, $id_client) {
    $stmt = $pdo->prepare("SELECT SUM(quantite) FROM commande WHERE id_client = ? AND statut = ?");
    $stmt->execute([$id_client, 'En attente']);
    return $stmt->fetchColumn() ?? 0;
}

// Check if the dismissal request came from this page
if (isset($_GET['dismiss_delivery_alert']) && $_GET['dismiss_delivery_alert'] == 'true') {
    $_SESSION['delivery_alert_dismissed'] = true;
    header('Location: ' . $current_page); // Redirect to remove the GET parameter
    exit();
}

// Reset the dismissal if the user logs out or if you want it to reappear after a certain time/action
// For this example, it stays dismissed until session ends or explicitly reset.
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAVY COUTURE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
     <style>
        /* In your css/style.css file */

.delivery-notification {
    background-color: #d4edda; /* Light green background */
    color: #155724; /* Dark green text */
    padding: 15px 20px;
    margin-bottom: 20px; /* Space below the notification */
    border: 1px solid #c3e6cb;
    border-radius: 5px;
    display: flex; /* For alignment of text and dismiss button */
    justify-content: space-between; /* Pushes dismiss button to the right */
    align-items: center;
    font-size: 1rem;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: relative; /* If you want to position it fixed, make it fixed */
    z-index: 1000; /* Ensure it's above other content */
}

.delivery-notification p {
    margin: 0; /* Remove default paragraph margin */
    flex-grow: 1; /* Allow text to take available space */
}

.delivery-notification .dismiss-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #155724;
    cursor: pointer;
    text-decoration: none; /* Remove underline from link */
    padding: 0 10px;
    transition: color 0.2s ease;
}

.delivery-notification .dismiss-btn:hover {
    color: #0c3e17; /* Darker green on hover */
}
     </style>
</head>
<body>


<header class="navbar">
        <img class="logo" src="img/lavy.jpg">
        <nav class="nav-links">
            <ul>
                <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Accueil</a></li>
            <li><a href="catalogue.php" class="<?= $current_page == 'catalogue.php' ? 'active' : '' ?>">Catalogue</a></li>
            <li><a href="create.php" class="<?= $current_page == 'create.php' ? 'active' : '' ?>">Créer votre modèle</a></li>
            <li><a href="apropos.php" class="<?= $current_page == 'apropos.php' ? 'active' : '' ?>">À propos</a></li>
            <li>
                <a href="detail_commande.php" class="<?= $current_page == 'detail_commande.php' ? 'active' : '' ?>" >
                    Mes commandes
                </a>
            </li>

            <li><a href="faq.php" class="<?= $current_page == 'faq.php' ? 'active' : '' ?>">FAQ</a></li>

                <?php if ($isLoggedIn): ?>
                    <li>
                        <a href="panier.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">
                                <?= (isset($_SESSION['id']) ? getPanierCount($pdo, $_SESSION['id']) : 0) ?>
                            </span>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="connexion.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <li><button class="button1"><i class="fas fa-sign-out-alt"></i><a href="deconnexion.php">Déconnexion</a> </button></a></li>
                <?php else: ?>
                    <li><button class="button1"><a href="connexion.php">Connexion</a> <i class="fa-solid fa-user"></i></button></a></li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                <li><a href="profil_client.php"><img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profil" />
                </a></li>

                <?php else: ?>

                    <?php endif; ?>

            </ul>
        </nav>
        <a href="#" class="menu-hamburger <?= ($isLoggedIn && hasDeliveredOrders($pdo, $_SESSION['id'])) ? 'has-alert' : '' ?>">
        <i class="fa-solid fa-bars"></i>
        </a>
    
        
</header>


    <script src="js/menu.js">
        document.querySelector(".menu-hamburger").addEventListener("click", function () {
        document.querySelector(".nav-links").classList.toggle("mobile-menu");
    });
    </script>


    <div class="banner">
        <video autoplay loop muted playsinline>
            <source src="video/video1.mp4" type="video/mp4">
        </video>
        
        <div class="content">
      
            <h1>Bienvenue chez LAVY COUTURE</h1>
            <button class="button2"><a href="apropos.php">En savoir plus sur nous</a></button>
        </div>
              
    <?php if ($isLoggedIn && hasOrdersReadyForDelivery($pdo, $_SESSION['id']) && !isset($_SESSION['delivery_alert_dismissed'])): ?>
    <div class="delivery-notification">
        <p>🎉 Bonne nouvelle ! Votre colis est prêt à être livré. Vérifiez vos commandes pour plus de détails.</p>
        <a href="?dismiss_delivery_alert=true" class="dismiss-btn">&times;</a>
    </div>
    <?php endif; ?>
    </div>


    <section class="catalogue">
        <h1 class="h1">Nos produits</h1>

        <div class="dame">
            <div class="products">
                <div class="card">
                    <img src="img/matur.jpg" alt="Robe dame">
                    <div class="desc">DAME MATURE</div>

                </div>
                <div class="card">
                    <img src="img/je.jpg" alt="Ensemble dame">
                    <div class="desc">JEUNE DAME</div>

                </div>
                <div class="card">
                    <img src="img/jeune.jpg" alt="Boubou dame">
                    <div class="desc">FILLETTE</div>

                </div>
            </div>
            <div class="box">
                        <button class="button3"><a href="catalogue.php">Plus de details</a></button>
            </div>
        </div>


    </section>

    <?php include 'footer.php'; ?>

</body>
</html>