<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['date_livraison'])) {
    $id_commande = intval($_POST['id_commande']);
    $date_livraison = $_POST['date_livraison'];

    if (DateTime::createFromFormat('Y-m-d', $date_livraison) !== false) {
        $sql = "UPDATE livraison SET date_livraison = :date_livraison WHERE id_commande = :id_commande";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['date_livraison' => $date_livraison, 'id_commande' => $id_commande]);
    }
}

header('Location: livreur.php'); // adapte vers ta page principale
exit;
