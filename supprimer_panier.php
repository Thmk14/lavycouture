<?php
require 'config.php';
require 'session.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM panier WHERE id_panier = ?");
    $stmt->execute([$_GET['id']]);
}

header("Location: panier.php");
exit();


?>