<?php
require 'config.php'; // connexion base de données

// Gestion des statuts de livraison
if (isset($_GET['id_commande']) && isset($_GET['statut'])) {
    $id_commande = intval($_GET['id_commande']);
    $statut_livraison = htmlspecialchars($_GET['statut']);

    try {
        // Mettre à jour le statut de livraison dans la table commande
        $sql = "UPDATE commande SET statut = :statut WHERE id_commande = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'statut' => $statut_livraison,
            'id' => $id_commande
        ]);

        // Rediriger vers la page livreur
        header('Location: dashboard_livreur.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

// Gestion de la mise à jour de la date de livraison
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_commande']) && isset($_POST['date_livraison'])) {
    $id_commande = intval($_POST['id_commande']);
    $date_livraison = htmlspecialchars($_POST['date_livraison']);

    try {
        // Mettre à jour la date de livraison dans la table commande
        $sql = "UPDATE commande SET date_livraison = :date WHERE id_commande = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'date' => $date_livraison,
            'id' => $id_commande
        ]);

        // Rediriger vers la page livreur
        header('Location: dashboard_livreur.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour de la date : " . $e->getMessage();
    }
}

// Gestion de l'ancien format pour compatibilité
if (isset($_GET['id']) && isset($_GET['statut'])) {
    $id_commande = intval($_GET['id']);
    $statut_livraison = htmlspecialchars($_GET['statut']);

    try {
        // Mettre à jour le statut de livraison dans la table commande
        $sql = "UPDATE commande SET statut = :statut WHERE id_commande = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'statut' => $statut_livraison,
            'id' => $id_commande
        ]);

        // Rediriger vers la page livreur
        header('Location: dashboard_livreur.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

// Si aucune action n'est spécifiée
echo "Aucune action spécifiée.";
?>
