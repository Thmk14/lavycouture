<?php
session_start();
require 'config.php';

// Vérification de connexion
if (!isset($_SESSION['id_client'])) {
    header('Location: connexion.php');
    exit();
}

$id_client = $_SESSION['id_client'];

// Vérification de l'identifiant de commande
if (!isset($_GET['id_commande'])) {
    echo "Commande introuvable.";
    exit();
}

$id_commande = intval($_GET['id_commande']);

// Vérifier que cette commande appartient bien au client connecté
$verif = $conn->prepare("SELECT * FROM commande WHERE id_commande = ? AND id_client = ?");
$verif->execute([$id_commande, $id_client]);

if ($verif->rowCount() === 0) {
    echo "Accès non autorisé.";
    exit();
}

// Récupérer les articles de cette commande
$requete = $conn->prepare("
    SELECT ca.*, v.nom AS nom_vetement, v.photo, v.prix 
    FROM commande_article ca
    JOIN vetement v ON ca.id_vetement = v.id_vetement
    WHERE ca.id_commande = ?
");
$requete->execute([$id_commande]);
$articles = $requete->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Détails de la commande</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Détail de la commande #<?= htmlspecialchars($id_commande) ?></h2>
    <a href="historique_commandes.php">← Retour à l'historique</a>

    <?php if (empty($articles)): ?>
        <p>Cette commande est vide.</p>
    <?php else: ?>
        <?php foreach ($articles as $article): ?>
            <div class="article">
                <img src="<?= htmlspecialchars($article['photo']) ?>" alt="Photo" width="150">
                <p><strong>Nom :</strong> <?= htmlspecialchars($article['nom_vetement']) ?></p>
                <p><strong>Quantité :</strong> <?= $article['quantite'] ?></p>
                <p><strong>Prix unitaire :</strong> <?= $article['prix'] ?> F</p>
                <p><strong>Personnalisation :</strong> <?= $article['personnalisation'] ? htmlspecialchars($article['personnalisation']) : 'Aucune' ?></p>
                <p><strong>Mensurations :</strong> <?= $article['mensuration'] ? htmlspecialchars($article['mensuration']) : 'Non spécifiées' ?></p>
                <p><strong>Total article :</strong> <?= $article['quantite'] * $article['prix'] ?> F</p>
                <form method="post" action="supprimer_article_commande.php" onsubmit="return confirm('Supprimer cet article ?')">
                    <input type="hidden" name="id_commande" value="<?= $id_commande ?>">
                    <input type="hidden" name="id_commande_article" value="<?= $article['id_commande_article'] ?>">
                    <button type="submit">Supprimer cet article</button>
                </form>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
