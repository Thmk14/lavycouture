<?php
include("config.php");
include("session.php");

if (!isset($_GET['id_client'])) {
    echo "Client non spécifié.";
    exit();
}

$id_client = $_GET['id_client'];

// Récupérer les infos client
$stmtClient = $pdo->prepare("SELECT nom, prenom FROM client WHERE id_client = ?");
$stmtClient->execute([$id_client]);
$client = $stmtClient->fetch(PDO::FETCH_ASSOC);
if (!$client) {
    echo "<p style='color:red; text-align:center;'>Client introuvable.</p>";
    exit;
}

$requete = "SELECT c.*, a.*, cmd.*, m.*
            FROM concerner c
            JOIN article a ON c.id_article = a.id_article 
            JOIN commande cmd ON c.id_commande = cmd.id_commande
            JOIN client cl ON cmd.id_client = cl.id_client
            JOIN mensuration m ON cmd.id_mensuration = m.id_mensuration
            WHERE cmd.id_client = ? AND visibilite = ?";
$prepare = $pdo->prepare($requete);
$prepare->execute([$id_client,1]);
$articles = $prepare->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Articles - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        h1 {
            text-align: center;
            color: #a72872;
            margin-top: 80px;
            font-size: 3em;
        }
        .add-button {
            display: block;
            width: 250px;
            margin: 20px auto;
            padding: 12px;
            background-color: #e462bd;
            color: white;
            font-size: 1.2em;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
        }
        .add-button:hover {
            background-color: #c74d9e;
        }
        .table-responsive {
            margin: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .table thead th {
            background-color: #bf59a2;
            color: white;
            padding: 15px;
            text-align: center;
        }
        .table tbody td {
            text-align: center;
            vertical-align: middle;
            padding: 12px;
        }
        .img-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
        }
        .action-icon {
            margin: 0 8px;
            font-size: 1.4em;
            color: #a72872;
        }
        .action-icon.edit:hover {
            color: #007bff;
        }
        .action-icon.delete:hover {
            color: #dc3545;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            max-width: 90%;
            max-height: 80vh;
            object-fit: contain;
            border-radius: 8px;
        }
        .close-modal {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 45px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
         <h1>Commandes de <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?></h1>
        <a href="ajout_atelier.php" class="add-button"><i class="fa-solid fa-plus"></i> Ajouter un nouvel article</a>
        <div class="table-responsive">
            <?php if (!$_SESSION['id'] || empty($articles)): ?>
                      <div class="text-center">Aucun article trouv&eacute;.</div>
                <?php else: ?>
          
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            
                            <th>Image Mod&egrave;le</th>
                            <th>Image Tissu</th>
                            <th>Description</th>
                            <th>Montant</th>
                            <th>Mensuration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articles as $article): ?>
                            <tr>
                                <td><img src="uploads/<?= ($article['image']) ?>" class="img-thumbnail" data-fullsrc="uploads/<?= htmlspecialchars($article['image']) ?>"></td>
                                <td>
                                    <?php if (!empty($article['tissu'])): ?>
                                        <img src="uploads/<?= htmlspecialchars($article['tissu']) ?>" class="img-thumbnail" data-fullsrc="uploads/<?= htmlspecialchars($article['tissu']) ?>">
                                    <?php else: ?>Aucun<?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($article["description_modele"] ?? 'Aucune') ?></td>
                                <td><?= number_format($article["montant_total"] ?? 0, 0, ',', ' ') ?> FCFA</td>
                                <td>
                                                    <a href="listmesurec.php?id_commande=<?= ($article['id_commande']) ?>" class="btn btn-prete">
                                                        Mensuration
                                                    </a>
                                                </td>
                                <td>
                                    <a href="modifarticle.php?id_article=<?= htmlspecialchars($article["id_article"]) ?>" class="action-icon edit"><i class="fa fa-pen"></i></a>
                                    <a href="suppvet.php?id_article=<?= htmlspecialchars($article["id_article"]) ?>" onclick="return confirm('Supprimer ?')" class="action-icon delete"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            
            <?php endif; ?>
        </div>
    </div>

    <div id="imageModal" class="modal">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="modalImage">
    </div>

    <script>
        const imageModal = document.getElementById("imageModal");
        const modalImage = document.getElementById("modalImage");
        const closeButton = document.querySelector(".close-modal");

        document.querySelectorAll(".img-thumbnail").forEach(img => {
            img.onclick = function () {
                imageModal.style.display = "flex";
                modalImage.src = this.dataset.fullsrc || this.src;
            };
        });

        closeButton.onclick = function () {
            imageModal.style.display = "none";
        };

        window.onclick = function (event) {
            if (event.target === imageModal) {
                imageModal.style.display = "none";
            }
        };
    </script>
</body>
</html>
