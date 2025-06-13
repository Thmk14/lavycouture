<?php
include("config.php");
include("session.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['id']);



// Récupération des filtres GET
$categorie_filtre = isset($_GET['categorie']) ? trim($_GET['categorie']) : '';
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Construction de la requête SQL avec filtre
$sql = "SELECT * FROM article WHERE 1=1";
$params = [];

if ($categorie_filtre && in_array($categorie_filtre, ['fillette', 'jeune dame', 'dame mature'])) {
    $sql .= " AND categorie = ?";
    $params[] = $categorie_filtre;
}

if ($search_term !== '') {
    $sql .= " AND nom_modele LIKE ?";
    $params[] = "%$search_term%";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Catalogue de vêtements</title>
    <link rel="stylesheet" href="css/catalogue.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
      /* Simple style pour le filtre et recherche */
      .filter-search-container {
        max-width: 1000px;
        padding-bottom:50px;
        margin: 20px auto;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        align-items: center;
      }
      .filter-search-container select,
      .filter-search-container input[type="search"] {
        padding: 8px 12px;
        font-size: 16px;
        border-radius: 5px;
        border: 1px solid #ccc;
      }
      .filter-search-container button {
        background-color: #a72872;
        color: white;
        border: none;
        padding: 9px 18px;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
      }
      .filter-search-container button:hover {
        background-color: #8f2163;
      }

      /* Responsive */
      @media (max-width: 600px) {
        .filter-search-container {
          flex-direction: column;
          gap: 15px;
        }
      }
    </style>
  </head>
  <body>
    <div>
      <?php include 'menu.php'; ?>
    </div>

   

    <!-- Catalogue Section -->
    <section class="catalogue">

    <h1 class="h1">CATALOGUE DE VÊTEMENTS</h1>

     <!-- Formulaire de filtre + recherche -->
    <div class="filter-search-container">
      <form method="GET" action="" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
        <label for="categorie_personne" style="font-weight:bold;">Catégorie :</label>
        <select name="categorie_personne" id="categorie_personne">
          <option value="">-- Toutes --</option>
          <option value="fillette" <?= ($categorie_filtre === 'fillette') ? 'selected' : '' ?>>Fillette</option>
          <option value="jeune dame" <?= ($categorie_filtre === 'jeune dame') ? 'selected' : '' ?>>Jeune dame</option>
          <option value="dame mature" <?= ($categorie_filtre === 'dame mature') ? 'selected' : '' ?>>Dame mature</option>
        </select>

        <label for="search" style="font-weight:bold;">Rechercher :</label>
        <input type="search" name="search" id="search" placeholder="Nom du modèle" value="<?= htmlspecialchars($search_term) ?>" />

        <button type="submit">Filtrer</button>
        <a href="catalogue.php" style="margin-left:10px; text-decoration:none; color:#a72872; font-weight:bold;">Réinitialiser</a>
      </form>
    </div>


      <div class="dame">
        <div class="products">
          <?php if (count($products) > 0): ?>
            <?php foreach ($products as $fetch_product): ?>
              <div class="card">
                <div class="img">
                  <img src="uploads/<?= htmlspecialchars($fetch_product['image']) ?>" alt="<?= htmlspecialchars($fetch_product['nom_modele']) ?>">
                </div>
                <div class="desc"><?= htmlspecialchars($fetch_product['categorie']) ?></div>
                <div class="title"><?= htmlspecialchars($fetch_product['nom_modele']) ?></div>
                <div class="titl"><?= htmlspecialchars($fetch_product['description']) ?></div>
                <div class="price"><?= htmlspecialchars($fetch_product['prix']) ?> FCFA</div>

                <?php if ($isLoggedIn): ?>
                  
                    <a href="detail.php?id_article=<?= $fetch_product['id_article'] ?>">
                      <button class="button3">Voir les détails</button>
                    </a>
                  
                <?php else: ?>
                  <a href="connexion.php">
                    <button class="button3">Voir les détails</button>
                  </a>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="text-align:center; font-size:18px; color:#555;">Aucun vêtement trouvé pour ces critères.</p>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <?php include 'footer.php'; ?>
  </body>
</html>
