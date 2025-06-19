<?php
require('config.php');
require('session.php');

// Vérifier la connexion
if (!isset($_SESSION['id'])) {
    header("Location: connexion.php");
    exit();
}

$id_client = $_SESSION['id'];

// Préparer la requête
$sql = $pdo->prepare("SELECT * FROM concerner
    JOIN commande ON concerner.id_commande = commande.id_commande
    JOIN article ON concerner.id_article = article.id_article
    JOIN client ON commande.id_client = client.id_client
    WHERE client.id_client = ?
    ORDER BY commande.date_commande DESC");

$sql->execute([$id_client]);
$commandes = $sql->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Commandes</title>
    <style>
        body {
  font-family: Arial, sans-serif;
  background:rgb(253, 200, 239);
  padding: 20px;
  margin: 0;
}

h2 {
  color: #a72872;
  margin-bottom: 30px;
  text-align: center;
  font-size: 35px;
}

.commandes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
  padding: 10px;
}

.commande {
  background: #fff;
  padding: 18px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  transition: transform 0.2s ease;
  margin: 0 30px;
}

.commande:hover {
  transform: translateY(-4px);
}

.commande p {
  margin: 8px 0;
  color: #444;
  font-size: 14px;
}




.btn-danger {
display: inline-block;
  padding: 10px 16px;
  background:rgb(216, 4, 181);
  width: 30%;
  color: #fff;
  border: none;
  border-radius: 6px;
  text-decoration: none;
  font-size: 14px;
  margin-top: 10px;
  transition: background 0.3s ease;
}


.btn-danger:hover {
  background:rgb(121, 21, 99);
}

/* Partie intérieure (article commandé) */
.commande-item {
  display: flex;
  gap: 15px;
  margin-top: 15px;
  padding: 14px;
  border-radius: 10px;
  background-color:rgb(252, 222, 245);
  align-items: flex-start;
  flex-wrap: wrap;
}

.commande-item img.img-thumbnail {
  height: 80px;
  width: 80px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.commande-item div {
  flex: 1;
  min-width: 220px;
}

.commande-item p {
  margin: 6px 0;
  font-size: 15px;
  color: #333;
  line-height: 1.5;
}

.commande-item p em {
  font-style: italic;
  color: #666;
}

.commande-item p strong {
  font-weight: bold;
  color: #a72872;
}

.commande-item img.tissu-img {
  height: 60px;
  width: 60px;
  object-fit: cover;
  margin-left: 10px;
  border-radius: 6px;
  border: 1px dashed #aaa;
}

/* Responsive */
@media (max-width: 600px) {
  .commande-item {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .commande-item img.img-thumbnail {
    margin-bottom: 10px;
  }

  .commande-item div {
    width: 100%;
  }
}


    </style>
</head>
<body>
    <h2>Historique de vos commandes</h2>
<div class="commandes-grid">
    <?php if (count($commandes) > 0): ?>
        <?php foreach ($commandes as $commande): ?>
            <div class="commande">
                <p><strong>Commande N° :</strong> <?= htmlspecialchars($commande['id_commande']) ?></p>
                <p><strong>Date de commande :</strong> <?= ($commande['date_commande']) ?></p>
                <p><strong>Date de livraison :</strong> <?=($commande['date_livraison']) ?></p>
                <p><strong>Statut :</strong> <?= ($commande['statut']) ?></p>
               <?php
// Calcul du prix total par article
$prix_unitaire = (float)$commande['prix'];
$supplement = (float)$commande['supplement_prix'];
$quantite = (int)$commande['quantite'];
$total = ($prix_unitaire + $supplement) * $quantite;
?>

<div class="commande-item">
  <img src="uploads/<?= htmlspecialchars($commande['image']) ?>" alt="Image Client" class="img-thumbnail" id="myImg">

  <div>
    <p><strong><?= htmlspecialchars($commande['nom_modele']) ?></strong></p>
    
    <p>
      <?= number_format($prix_unitaire, 0, ',', ' ') ?> FCFA × <?= $quantite ?>
      <?php if ($supplement > 0): ?>
        + <?= number_format($supplement, 0, ',', ' ') ?> FCFA supplément
      <?php endif; ?>
      = <strong><?= number_format($total, 0, ',', ' ') ?> FCFA</strong>
    </p>

    <?php if (!empty($commande['description_modele'])): ?>
      <p><em>Description :</em> <?= htmlspecialchars($commande['description_modele']) ?></p>
    <?php endif; ?>

    <?php if (!empty($commande['tissu'])): ?>
      <p><em>Tissu :</em> <img src="uploads/<?= htmlspecialchars($commande['tissu']) ?>" alt="Tissu" class="img-thumbnail tissu-img" id="myImg"></p>
    <?php endif; ?>
  </div>
</div>


                <a class=" btn-danger" href="supprimer_commande.php?id=<?= urlencode($commande['id_commande']) ?>" onclick="return confirm('Confirmer la suppression de la commande ?')">Supprimer la commande</a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucune commande trouvée.</p>
    <?php endif; ?>

    </div>
</body>
</html>
