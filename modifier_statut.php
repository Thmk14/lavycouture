<?php
require 'config.php';
session_start();

if (isset($_POST['id_commande'], $_POST['nouveau_statut'])) {
    $id = $_POST['id_commande'];
    $statut = $_POST['nouveau_statut'];

    $stmt = $pdo->prepare("UPDATE commande SET statut_livraison = ? WHERE id_commande = ?");
    $stmt->execute([$statut, $id]);
}

header("Location: dashboard_livreur.php");
exit;
