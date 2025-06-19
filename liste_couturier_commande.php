<?php
require 'config.php';
session_start();

$id_client = $_SESSION['id'];
$sql = "SELECT *
                  FROM concerner c
                  JOIN article art ON c.id_article = art.id_article
                  JOIN commande cmd ON c.id_commande = cmd.id_commande
                    JOIN client cl ON cmd.id_client = cl.id_client
                 JOIN mensuration m ON cmd.id_mensuration= m.id_mensuration
                  WHERE cmd.id_client = ? ";
  
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_client]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Commandes à Préparer</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 16px;
            padding: 20px;
            margin: 0;
        }

        img {
            width: 70px;
            border-radius: 6px;
        }

        .total {
            text-align: right;
            font-weight: bold;
            color: #a72872;
            margin-top: 10px;
        }

        h1, h3 {
            text-align: center;
            color: #a72872;
        }

        h1 {
            font-size: 50px;
            margin: 50px 0;
        }

        h3 {
            font-size: 30px;
            margin: 50px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #a72872;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #fce7f3;
        }

        .check {
            font-size: 18px;
            color: green;
        }

        .div {
            width: 100%;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            border: none;
        }

        .btn-valider { background-color: gray; }
        .btn-annuler { background-color: #f57c00; }
        .btn-prete { background-color: #4CAF50; }

        td form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            max-width: 500px;
            width: 100%;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .img-thumbnail {
            width: 100px;
            height: auto;
            cursor: pointer;
        }

        @media screen and (max-width: 768px) {
            table {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                font-size: 12px;
                padding: 8px;
            }

            .btn {
                padding: 4px 8px;
                font-size: 12px;
            }

            h1 {
                font-size: 24px;
            }

            h3 {
                font-size: 18px;
            }

            .img-thumbnail {
                width: 60px;
            }

            .modal-content {
                width: 90%;
            }
        }

        @media screen and (max-width: 480px) {
            .btn {
                display: block;
                width: 100%;
                margin-bottom: 5px;
            }

            .img-thumbnail {
                width: 50px;
            }

            th, td {
                font-size: 10px;
            }

            h1, h3 {
                font-size: 18px;
            }

            .modal-content {
                width: 95%;
            }
        }
    </style>
</head>
<body>

<?php include 'catmenuc.php'; ?>

<div class="main-content">
<h1>Commandes en attente de confection</h1>




<?php
if (count($commandes) > 0): ?>

<h3>Client: <?= ($commandes[0]['prenom'] . ' ' . $commandes[0]['nom']) ?></h3>
   <?php 
    $grouped = [];
    foreach ($commandes as $cmd) {
        $key = $cmd['id_client'] . '_' . $cmd['id_commande'];
        $grouped[$key]['client'] = $cmd;
        $grouped[$key]['articles'][] = [
            'image' => $cmd['image'],
            'nom_modele' => $cmd['nom_modele'],
            'quantite' => $cmd['quantite'],
            'taille_standard' => $cmd['taille_standard'],
            'tissu' => $cmd['tissu'],
            'description_modele' => $cmd['description_modele'],
            'prix' => $cmd['prix'],
            'id_commande' => $cmd['id_commande']

        ];
    }

    foreach ($grouped as $key => $commande):
        $client = $commande['client'];
?>

<div class="div">
<table>
    <tr>
        <th>Commande</th>
        <th>Adresse</th>
        <th>Téléphone</th>
        <th>Statut</th>
        <th>Date commande</th>
        <th>Date Livraison souhaitée</th>
        
        <th>Actions</th>
    </tr>
    <tr>
        <td>#<?= $client['id_commande'] ?></td>
     
        <td><?= ($client['lieu_habitation']) ?></td>
        <td><?= ($client['telephone']) ?></td>
        <td><?= ($client['statut']) ?></td>
        <td><?= ($client['date_commande']) ?></td>
        <td><?= ($client['date_livraison']) ?></td>
        <td>
            <form method="POST" action="marquer_prete.php">
                <input type="hidden" name="id_commande" value="<?= $client['id_commande'] ?>">
                <button class="btn btn-valider" name="action" value="en attente">En attente</button>
                <button class="btn btn-annuler" name="action" value="en préparation">En préparation</button>
                <button class="btn btn-prete" name="action" value="prête">Prête</button>
            </form>
            
        </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Modèle</th>
            <th>Type tissu</th>
            <th>Quantité</th>
            <th>Taille</th>
            <th>Personnalisation</th>
            <th>Prix unitaire</th>
            <th>Total</th>
            <th>Mensurations</th>
        </tr>
    </thead>
    <tbody>
        <?php $total = 0; ?>
        <?php foreach ($commande['articles'] as $item): ?>
            <?php $sous_total = $item['quantite'] * $item['prix']; $total += $sous_total; ?>
            <tr>
                <td>
                    <?php if (!empty($item['image'])): ?>
                        <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Image Client" class="img-thumbnail">
                    <?php else: ?>
                        <span style="color:gray;">Aucune image</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['nom_modele']) ?></td>
                <td><img src="uploads/<?= ($item['tissu']) ?>" alt="Tissu" class="img-thumbnail"></td>
                <td><?= $item['quantite'] ?></td>
                <td><?= ($item['taille_standard']) ?></td>
                <td><?= ($item['description_modele']) ?></td>
                <td><?= $item['prix'] ?> FCFA</td>
                <td><?= $sous_total ?> FCFA</td>
             <td>
  <a href="listmesurec.php?id_commande=<?= htmlspecialchars($item['id_commande']) ?>" class="btn btn-prete">
    Mensuration
  </a>
</td>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<p class="total">Total : <?= $total ?> FCFA</p>

</div>


<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
</div>
<?php endforeach; ?>
<?php else: ?>
<p style="text-align:center; font-size:30px; color:#a72872;">Aucune commande enregistrée pour le moment.</p>
<?php endif; ?>

</div>
<script>
var modal = document.getElementById("myModal");
var modalImg = document.getElementById("img01");
document.querySelectorAll(".img-thumbnail").forEach(function(img) {
    img.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.src;
    };
});
document.querySelector(".close").onclick = function() {
    modal.style.display = "none";
};
</script>
</body>
</html>
