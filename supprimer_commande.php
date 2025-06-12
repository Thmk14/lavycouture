<?php
require 'config.php';
session_start();

if (isset($_GET['id_commande'])) {
    $id_commande = $_GET['id_commande'];

    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Récupérer l'ID client lié à la commande
        $sql0 = "SELECT id_client FROM commande WHERE id_commande = :id_commande";
        $stmt0 = $pdo->prepare($sql0);
        $stmt0->execute(['id_commande' => $id_commande]);
        $result = $stmt0->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $id_client = $result['id_client'];

            // Supprimer les articles du panier pour ce client
            $sql1 = "DELETE FROM panier WHERE id_client = :id_client";
            $stmt1 = $pdo->prepare($sql1);
            $stmt1->execute(['id_client' => $id_client]);

           // Supprimer la commande
$sql2 = "DELETE FROM commande WHERE id_commande = :id_commande";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute(['id_commande' => $id_commande]);

// Déverrouiller le panier du client
$sql3 = "UPDATE client SET panier_lock = 0 WHERE id_client = :id_client";
$stmt3 = $pdo->prepare($sql3);
$stmt3->execute(['id_client' => $id_client]);

            $pdo->commit();
            header("Location: listcom.php?message=Commande supprimée avec succès");
            exit();
        } else {
            throw new Exception("Commande non trouvée.");
        }

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erreur lors de la suppression : " . $e->getMessage();
    }
} else {
    echo "Aucun ID de commande spécifié.";
}
var_dump($id_commande);
exit();
