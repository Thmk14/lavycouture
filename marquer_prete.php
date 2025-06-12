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
    $sql_update_commande = "UPDATE commande SET statut_commande = ? WHERE id_commande = ?";
    $stmt_update_commande = $pdo->prepare($sql_update_commande);
    $stmt_update_commande->execute([$statut_commande, $id_commande]);

    // 2. Vérifier si une entrée de livraison existe déjà pour cette commande dans la table 'livraison'
    $sql_check_livraison = "SELECT COUNT(*) FROM livraison WHERE id_commande = ?";
    $stmt_check_livraison = $pdo->prepare($sql_check_livraison);
    $stmt_check_livraison->execute([$id_commande]);
    $livraison_exists = $stmt_check_livraison->fetchColumn();

    if ($livraison_exists > 0) {
        // Si la livraison existe, la mettre à jour
        // Nous allons mettre à jour le statut de livraison ici.
        // Vous devrez décider quel statut de livraison est approprié à ce stade.
        // Par exemple, si la commande est mise à 'prête', le statut de livraison pourrait être 'en attente'.
        $nouveau_statut_livraison = 'en attente'; // Ou un autre statut logique en fonction de $statut_commande

        $sql_update_livraison = "UPDATE livraison SET statut_livraison = ? WHERE id_commande = ?";
        $stmt_update_livraison = $pdo->prepare($sql_update_livraison);
        $stmt_update_livraison->execute([$nouveau_statut_livraison, $id_commande]);
    } else {
        // Si la livraison n'existe pas, la créer
        // Lorsque la livraison est créée, on l'initialise avec un statut.
        $statut_initial_livraison = 'en attente'; // Statut initial pour une nouvelle livraison

        // Assurez-vous que toutes les colonnes NOT NULL de votre table 'livraison' sont incluses ici.
        // Par exemple, si vous avez 'id_livreur' ou 'date_livraison_prevue', vous devez les ajouter.
        // Pour cet exemple, nous supposons seulement 'id_commande' et 'statut_livraison'.
        $sql_insert_livraison = "INSERT INTO livraison (id_commande, statut_livraison) VALUES (?, ?)";
        $stmt_insert_livraison = $pdo->prepare($sql_insert_livraison);
        $stmt_insert_livraison->execute([$id_commande, $statut_initial_livraison]);
    }

    // Redirige toujours l'utilisateur après le traitement
    header("Location: listcomc.php");
    exit;
}
?>