<?php
require 'config.php';
require 'session.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_commande = $_POST['id_commande'] ?? null;
    $statut = $_POST['statut'] ?? null;
    $avance = $_POST['avance'] ?? null;
    $id_client = $_SESSION['id'] ?? null;

    if (!$id_commande || $statut === null || $avance === null || !$id_client) {
        // Rediriger avec un message d’erreur
        header("Location: liste_admin_commande.php?error=1");
        exit();
    }

    $avance = (int)$avance;

    try {
        $stmt = $pdo->prepare("UPDATE commande SET statut = ?, avance = ? WHERE id_commande = ? AND id_client = ?");
        $stmt->execute([$statut, $avance, $id_commande, $id_client]);

        // Redirection vers la liste des commandes avec un message de succès
        header("Location: liste_admin_commande.php?success=1");
        exit();

    } catch (PDOException $e) {
        error_log("Erreur PDO dans update_admin_commande.php: " . $e->getMessage());
        // Rediriger avec un message d’erreur
        header("Location: liste_admin_commande.php?error=1");
        exit();
    }

} else {
    // Requête non autorisée
    header("Location: liste_admin_commande.php?error=1");
    exit();
}
