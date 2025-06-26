<?php
require 'config.php';

// Affiche toutes les erreurs pour le débogage (désactivez en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_commande = $_POST['id_commande'];
    $statut_commande = $_POST['action']; // Renommé 'action' en 'statut_commande' pour plus de clarté

    // 1. Mettre à jour le statut de la commande dans la table 'commande'
    $sql_update_commande = "UPDATE commande SET statut = ? WHERE id_commande = ?";
    $stmt_update_commande = $pdo->prepare($sql_update_commande);
    $stmt_update_commande->execute([$statut_commande, $id_commande]);

   
   
    // Redirige toujours l'utilisateur après le traitement
    header("Location: liste_couturier_commande.php");
    exit;
}
?>