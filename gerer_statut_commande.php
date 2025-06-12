<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';
require 'session.php'; // Assurez-vous que ce fichier gère session_start() et les sessions client/personnel

// Vérifier si l'utilisateur est autorisé (Couturier ou Administrateur)
if (!isset($_SESSION['id_personnel']) || ($_SESSION['fonction'] !== 'couturier' && $_SESSION['fonction'] !== 'administrateur')) {
    $_SESSION['message_error'] = "Accès non autorisé à cette action.";
    header("Location: index.php"); // Rediriger vers une page appropriée
    exit();
}

// Vérifier si la requête est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message_error'] = "Méthode de requête invalide.";
    header("Location: listcomc.php");
    exit();
}

// Récupérer et valider les données POST
$id_commande = filter_input(INPUT_POST, 'id_commande', FILTER_VALIDATE_INT);
$nouveau_statut = filter_input(INPUT_POST, 'nouveau_statut', FILTER_SANITIZE_STRING);

// Définir les statuts de commande valides
$statuts_valides = [
    'En attente de confection',
    'En confection',
    'Confection terminée',
    'Annulé' // Le couturier peut aussi marquer une commande comme annulée
];

if ($id_commande === false || $id_commande <= 0) {
    $_SESSION['message_error'] = "ID de commande invalide.";
    header("Location: listcomc.php");
    exit();
}

if (!in_array($nouveau_statut, $statuts_valides)) {
    $_SESSION['message_error'] = "Statut de commande invalide.";
    header("Location: listcomc.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Vérifier si la commande existe
    $stmt_check_commande = $pdo->prepare("SELECT id_commande FROM commande WHERE id_commande = ?");
    $stmt_check_commande->execute([$id_commande]);
    if (!$stmt_check_commande->fetch()) {
        $pdo->rollBack();
        $_SESSION['message_error'] = "La commande #{$id_commande} n'existe pas.";
        header("Location: listcomc.php");
        exit();
    }

    // 2. Mettre à jour le statut de commande
    $stmt_update_statut = $pdo->prepare("UPDATE commande SET statut_commande = ? WHERE id_commande = ?");
    $stmt_update_statut->execute([$nouveau_statut, $id_commande]);

    $pdo->commit();

    $_SESSION['message_success'] = "Statut de la commande #{$id_commande} mis à jour à '{$nouveau_statut}' avec succès.";
    header("Location: listcomc.php");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack(); // Annuler la transaction en cas d'erreur
    error_log("Erreur lors de la mise à jour du statut de commande #{$id_commande} : " . $e->getMessage());
    $_SESSION['message_error'] = "Une erreur est survenue lors de la mise à jour du statut de la commande. Veuillez réessayer.";
    header("Location: listcomc.php");
    exit();
}
?>