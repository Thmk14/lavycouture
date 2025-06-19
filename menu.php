<?php
require('config.php');
require('session.php');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);


$isLoggedIn = isset($_SESSION['id']);

/*function hasDeliveredOrders($pdo, $id_client) {
    $stmt = $pdo->prepare("SELECT COUNT(*) 
        FROM livraison l 
        JOIN commande c ON l.id_livraison = c.id_livraison 
        WHERE c.id_client = ? AND l.statut_livraison = 'livrée'");
    $stmt->execute([$id_client]);
    return $stmt->fetchColumn() > 0;
}*/

function getPanierCount($pdo, $id_client) {
   
    $stmt = $pdo->prepare("SELECT SUM(quantite) FROM commande WHERE id_client = ?");
    $stmt->execute([$id_client]);
    return $stmt->fetchColumn() ?? 0;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        *{
            margin:0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
            text-decoration: none;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 20px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: transparent;
            transition: 0.6s;
            background: rgb(248, 117, 213);
            margin-bottom: 100px;
            z-index: 999;
        }

        .nav-links ul {
            display: flex;
            list-style: none;
        }

        .nav-links ul li {
            list-style: none;
            display: inline-block;
            margin: 10px ;
        }

        .nav-links ul li a {
            text-decoration: none;
            text-transform: uppercase;
            color: rgb(132, 1, 93);
            font-weight: 600;
            font-size:15px;
        }

        .nav-links ul li a.active {
    color: white;
    background-color:rgb(212, 10, 175);
    padding: 5px 10px;
    border-radius: 5px;
    font-weight: bold;
    box-shadow: 0 0 10px rgba(0,0,0,0.15);
}


        .nav-links ul li a:hover {
            color: black;
        }

        .button1 {
            background-color: rgba(248, 172, 233, 0.979);
            border-radius: 10px;
            padding: 10px 1px;
            width: 200px;
            border: none;
        }

        .button1 a {
            color: black;
            font-weight: bold;
            font-size: 20px;
        }

        .button1 i{
            color: rgb(132, 1, 93);
            font-size: 20px;
        }

        /* Logo */
        .logo {
            width: 100px;
            height: 100px;
            margin-top: 1px;
            cursor: pointer;
            position: relative;
        }

        /* Menu hamburger */
        .menu-hamburger {
            display: none;
            font-size: 30px;
            color: white;
            cursor: pointer;
            z-index: 1500; 
        }

        .menu-hamburger.has-alert::after {
    content: '';
    position: absolute;
    top: 38px;
    right: 11px;
    width: 10px;
    height: 10px;
    background-color: red;
    border-radius: 50%;
    z-index: 2000;
    box-shadow: 0 0 5px rgba(0,0,0,0.3);
}


        /* Responsive */
        @media screen and (max-width: 1200px) {
            .navbar {
                padding: 15px;
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100vh;
                background: rgba(251, 155, 214, 0.8);
                backdrop-filter: blur(10px);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transform: translateX(-100%);
                transition: transform 0.5s ease;
            }
            
            .nav-links ul {
                flex-direction: column;
            }
            
            .nav-links ul li {
                margin: 10px 2px;
                font-size: 20px;
                text-align: center;
            }
            
            .menu-hamburger {
                display: block;
            }

            .mobile-menu {
                transform: translateX(0);
            }
        }

        /* Icônes */
        .icons i {
            font-size: 35px;
            color: rgb(115, 9, 83);
            margin-left: 20px;
            transition: 0.3s;
        }

        .icons i:hover {
            color: #1b0212;
        }

        .cart-icon {
            position: relative;
            display: inline-block;
            background-color: #ff41c3;
            color: white;
            padding: 10px 12px;
            border-radius: 50%;
            text-decoration: none;
            font-size: 18px;
            transition: 0.3s ease;
        }

        .cart-icon:hover {
            background-color: #e137aa;
        }

        .cart-icon i {
            font-size: 20px;
        }

        .cart-count {
            position: absolute;
            top: -6px;
            right: -6px;
            background-color: white;
            color: #ff41c3;
            font-size: 13px;
            font-weight: bold;
            border-radius: 50%;
            padding: 4px 7px;
            border: 2px solid #ff41c3;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

        


.highlighted-command {
    color: #ff41c3; /* même rose flashy que ta charte */
    font-weight: bold;
    position: relative;
}

.highlighted-command::after {
    content: '•';
    color: red;
    font-size: 30px;
    position: absolute;
    top: -5px;
    right: -2px;
}

li a img {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 50%; /* rend l'image ronde */
    background-color: #fdeaf5; /* rose clair pour ton thème Lavit Couture */
    padding: 5px;
    transition: transform 0.3s ease;
}

li a img:hover {
    transform: scale(1.1); /* effet zoom au survol */
    background-color: #f5c2d6; /* un rose un peu plus foncé au hover */
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
            <li><a href="contact.php" class="<?= $current_page == 'contact.php' ? 'active' : '' ?>">Contact</a></li>
            <li><a href="faq.php" class="<?= $current_page == 'faq.php' ? 'active' : '' ?>">FAQ</a></li>

            <?php if ($isLoggedIn): ?>
                <li>
                    <a href="panier.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">
                                <?=  (isset($_SESSION['id']) ? getPanierCount($pdo, $_SESSION['id']) : 0) ?>
                            </span>
                    </a>
                </li>
                <li><button class="button1"><a href="deconnexion.php">Déconnexion</a> <i class="fas fa-sign-out-alt"></i></button></li>
                <li><a href="profil_client.php"><img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profil" /></a></li>
            <?php else: ?>
                <li>
                    <a href="connexion.php" class="cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </li>
                <li><button class="button1"><a href="connexion.php">Connexion</a> <i class="fa-solid fa-user"></i></button></li>

                <?php endif; ?>
                

        </ul>
    </nav>
    <a href="#" class="menu-hamburger <!?= ($isLoggedIn && hasDeliveredOrders($pdo, $_SESSION['id'])) ? 'has-alert' : '' ?>">
        <i class="fa-solid fa-bars"></i>
    </a>
</header>


<script>
    document.querySelector(".menu-hamburger").addEventListener("click", function () {
        document.querySelector(".nav-links").classList.toggle("mobile-menu");
    });
</script>

</body>
</html>
