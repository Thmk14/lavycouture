<?php
require 'config.php';
session_start();

$id_commande = $_POST['id_commande'];
$tour_poitrine = $_POST['tour_poitrine'];
$tour_taille = $_POST['tour_taille'];

// Vérifier si déjà une mensuration liée
$stmt = $pdo->prepare("SELECT id_mensuration FROM commande WHERE id_commande = ?");
$stmt->execute([$id_commande]);
$id_mensuration = $stmt->fetchColumn();

if ($id_mensuration) {
    // Mettre à jour
    $stmt = $pdo->prepare("UPDATE mensuration SET tour_poitrine = ?, tour_taille = ? WHERE id_mensuration = ?");
    $stmt->execute([$tour_poitrine, $tour_taille, $id_mensuration]);
} else {
    // Créer et lier
    $stmt = $pdo->prepare("INSERT INTO mensuration (tour_poitrine, tour_taille) VALUES (?, ?)");
    $stmt->execute([$tour_poitrine, $tour_taille]);
    $new_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("UPDATE commande SET id_mensuration = ? WHERE id_commande = ?");
    $stmt->execute([$new_id, $id_commande]);
}

header("Location: commande_atelier.php");
exit();
