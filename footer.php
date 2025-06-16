<?php 
require 'config.php'; 
require 'session.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        

        footer {
            background-color: rgb(248, 117, 213);
        }

        .footerContainer {
            width: 100%;
            padding: 70px 30px 20px;
        }

        .socialIcons {
            display: flex;
            justify-content: center;
        }

        .socialIcons a {
            text-decoration: none;
            padding: 10px;
            background-color: white;
            margin: 10px;
            border-radius: 50%;
        }

        .socialIcons a i {
            font-size: 2em;
            color: rgba(204, 48, 144, 0.96);
            opacity: 0.9;
        }

        .socialIcons a:hover {
            background-color: rgba(228, 88, 174, 0.96);
            transition: 0.5s;
        }

        .socialIcons a:hover i {
            color: white;
            transition: 0.5s;
        }

        .footerNav {
            margin: 30px 0;
        }

        .footerNav ul {
            display: flex;
            justify-content: center;
            list-style-type: none;
        }

        .footerNav ul li a {
            color: white;
            margin: 20px;
            text-decoration: none;
            font-size: 1.3em;
            opacity: 0.7;
            transition: 0.5s;
        }

        .footerNav ul li a:hover {
            opacity: 1;
        }

        .footerBottom {
            background-color: rgba(130, 12, 114, 0.96);
            padding: 20px;
            text-align: center;
        }

        .footerBottom h3 {
            color: white;
        }

        .designer {
            opacity: 0.7;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 400;
            margin: 0px 5px;
        }

        @media (max-width: 700px) {
            .footerNav ul {
                flex-direction: column;
            }
            .footerNav ul li {
                width: 100%;
                text-align: center;
                margin: 10px;
            }
            .socialIcons a {
                padding: 8px;
                margin: 4px;
            }
        }
    </style>
</head>
<body>
    <footer>
        <div class="footerContainer">
            <div class="socialIcons">
                <a href="https://www.facebook.com/share/19MxvED8Vi/"><i class="fa-brands fa-facebook"></i></a>
                <a href="https://www.instagram.com/lavy_couture?igsh=MXRpZmx4OW43NGVwbg=="><i class="fa-brands fa-instagram"></i></a>
                <a href="https://www.tiktok.com/@lavy.couture?_t=ZM-8vw8wAk25Bu&_r=1"><i class="fa-brands fa-tiktok"></i></a>
            </div>
            <div class="footerNav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="apropos.php">A propos</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
        <div class="footerBottom">
            <h3>Copyright &copy; 2025;<?php if ($isLoggedIn): ?>Designed<?php else: ?><a href="personnelog.php" style="color:white;">Designed </a><?php endif; ?> by <span class="designer">Lavy couture</span></h3>
        </div>
    </footer>
</body>
</html>