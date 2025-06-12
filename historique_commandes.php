<?php
// historique_commandes.php
session_start();
include("connexion.php");

if (!isset($_SESSION['id_client'])) {
    header("Location: connexion.php");
    exit();
}

$id_client = $_SESSION['id_client'];

$req = $conn->prepare("SELECT * FROM commande WHERE id_client = ? ORDER BY date_commande DESC");
$req->execute([$id_client]);
$commandes = $req->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Commandes</title>
    <style>
        body { font-family: Arial; background: #f9f9f9; padding: 20px; }
        .commande { background: white; margin-bottom: 20px; padding: 15px; border-radius: 10px; box-shadow: 0 0 8px #ccc; }
        .btn { padding: 8px 15px; background: #007BFF; color: white; border: none; border-radius: 5px; text-decoration: none; margin-right: 10px; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>
    <h2>Historique de vos commandes</h2>

    <?php foreach ($commandes as $commande): ?>
        <div class="commande">
            <p><strong>Commande N°:</strong> <?= $commande['id'] ?></p>
            <p><strong>Date:</strong> <?= $commande['date_commande'] ?></p>
            <p><strong>Statut:</strong> <?= $commande['statut'] ?></p>
            <a class="btn" href="detail_commande.php?id=<?= $commande['id'] ?>">Voir Détail</a>
            <a class="btn btn-danger" href="supprimer_commande.php?id=<?= $commande['id'] ?>" onclick="return confirm('Confirmer la suppression de la commande ?')">Supprimer la commande</a>
        </div>
    <?php endforeach; ?>
</body>
</html>
