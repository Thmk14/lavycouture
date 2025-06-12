<?php
require 'config.php';
require 'session.php';

$id_client = $_SESSION['id_client'];

$stmt = $pdo->prepare("SELECT * FROM panier WHERE id_client = ?");
$stmt->execute([$id_client]);
$items = $stmt->fetchAll();

foreach ($items as $item) {
    $insert = $pdo->prepare("INSERT INTO commande (id_client, id_vetement, quantite, personnalisation, date_commande) 
                             VALUES (?, ?, ?, ?, NOW())");
    $insert->execute([
        $id_client,
        $item['id_vetement'],
        $item['quantite'],
        $item['personnalisation']
    ]);
}

// Vider le panier
$pdo->prepare("DELETE FROM panier WHERE id_client = ?")->execute([$id_client]);

header("Location: index.php");
exit();
