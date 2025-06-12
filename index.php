<?php

require('config.php'); // Connexion à la base de données
require('session.php'); // Gérer la session utilisateur
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id_client']);

/*function hasDeliveredOrders($pdo, $id_client) {
    $sql="SELECT COUNT(*) 
        FROM livraison l 
        JOIN commande c ON l.id_livraison = c.id_livraison 
        WHERE c.id_client = ? AND l.statut_livraison = 'livrée'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_client]);
    return $stmt->fetchColumn() > 0;
}*/

/*function getPanierCount($pdo, $id_client) {
    $stmt = $pdo->prepare("SELECT panier_lock FROM client WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $lock = $stmt->fetchColumn();

    if ($lock == 1) return 0;

    $stmt = $pdo->prepare("SELECT SUM(quantite) FROM panier WHERE id_client = ?");
    $stmt->execute([$id_client]);
    return $stmt->fetchColumn() ?? 0;
}*/
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAVY COUTURE</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     
</head>
<body>
    
<header class="navbar">
        <img class="logo" src="img/lavy.jpg">
        <nav class="nav-links">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="catalogue.php">Catalogue</a></li>
                <li><a href="create.php">Créer votre modèle</a></li>
                <li><a href="apropos.php">À propos</a></li>
                <!--li>
                   <a href="detail_commande.php" class="<--?= (isset($_SESSION['id_client']) && hasDeliveredOrders($pdo, $_SESSION['id_client'])) ? 'highlighted-command' : '' ?>">
                       Mes commandes
                   </a>
               </-li -->

                <li><a href="contact.php">Contact</a></li>
                <li><a href="chatbot.php">FAQ</a></li>

                <!--?php if ($isLoggedIn): ?>
                    <li>
                        <a href="panier.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">
                                <--?= (isset($_SESSION['panier_lock']) && $_SESSION['panier_lock']) ? 0 : (isset($_SESSION['id_client']) ? getPanierCount($pdo, $_SESSION['id_client']) : 0) ?>
                            </span>
                        </a>
                    </li>
                <!?php else: ?>
                    <li>
                        <a href="connexion.php" class="cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </li>
                <!?php endif; ?-->

                <?php if ($isLoggedIn): ?>
                    <li><button class="button1"><i class="fas fa-sign-out-alt"></i><a href="deconnexion.php">Déconnexion</a> </button></a></li>
                <?php else: ?>
                    <li><button class="button1"><a href="connexion.php">Connexion</a> <i class="fa-solid fa-user"></i></button></a></li>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                <li><a href="profil_client.php"><img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profil"  />
                </a></li>
               
                <?php else: ?>
                    
                    <?php endif; ?>

            </ul>
        </nav>
        <!--a href="#" class="menu-hamburger <!?= ($isLoggedIn && hasDeliveredOrders($pdo, $_SESSION['id_client'])) ? 'has-alert' : '' ?>">
        <i class="fa-solid fa-bars"></i>
    </!--a-->
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

