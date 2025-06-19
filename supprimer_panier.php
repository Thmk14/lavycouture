<?php
require 'config.php';
require 'session.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM commande WHERE id_commande= ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: panier.php");
exit();


?>