<?php
require 'config.php';
session_start();

// Requête pour récupérer les commandes avec détails
$sql = "SELECT 
            c.id_client, c.nom, c.prenom, c.email, c.telephone, c.lieu_habitation,
            cmd.id_commande, cmd.mode_paiement, cmd.date_commande, cmd.date_liv_souhait, cmd.statut_commande,
            p.*,
            liv.date_livraison, liv.statut_livraison,
            v.nom_modele, v.image, v.prix
        FROM client c
        JOIN commande cmd ON cmd.id_client = c.id_client
        JOIN panier p ON p.id_client = c.id_client
        JOIN vetement v ON p.id_vetement = v.id_vetement
        LEFT JOIN livraison liv ON liv.id_commande = cmd.id_commande
        ORDER BY cmd.date_commande DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commandes Clients - Admin</title>
    <style>
        body { margin: 0; padding: 0; }
        h1 {
            text-align: center;
            color: #a72872;
            margin: 60px;
            font-size: 40px;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }
        .commande-block {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 30px 50px;
        }
        .commande-block h3 {
            color: #a72872;
            margin-bottom: 15px;
        }
        .commande-block p {
            margin: 5px 0;
            font-size: 15px;
            color: #333;
        }
        table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #eee;
            text-align: center;
        }
        th {
            background-color: #f8c4e6;
            color: white;
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
        .btn-delete {
            display: inline-block;
            padding: 10px;
            background-color: rgb(241, 98, 186);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
        }
        .img-thumbnail {
            width: 100px;
            cursor: pointer;
        }
       .status {
    font-weight: bold;
    font-size: 15px;
    position: relative;
    display: inline-block;
}

.status.en-attente {
    color: gray;
    animation: pulseText 1.5s infinite;
}

.status.en-preparation {
    color: orange;
    animation: pulseText 1.5s infinite;
}

.status.en-route {
    color: #e19f04;
    animation: pulseText 1.5s infinite;
}

.status.en-retrait {
    color: #44bbe0;
    animation: pulseText 1.5s infinite;
}

.status.en-livree {
    color: green;
}

.status.unknown {
    color: #999;
}

@keyframes pulseText {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

        
    </style>
</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="main-content">
<h1>Commandes des Clients</h1>

<?php
if (count($commandes) > 0):
    $grouped = [];

    foreach ($commandes as $cmd) {
        $key = $cmd['id_client'] . '_' . $cmd['id_commande'];

        if (!isset($grouped[$key])) {
            $grouped[$key]['client'] = [
                'nom' => $cmd['nom'],
                'prenom' => $cmd['prenom'],
                'email' => $cmd['email'],
                'telephone' => $cmd['telephone'],
                'lieu_habitation' => $cmd['lieu_habitation']
            ];

           $grouped[$key]['commande'] = [
    'id_commande' => $cmd['id_commande'],
    'mode_paiement' => $cmd['mode_paiement'],
    'date_liv_souhait' => $cmd['date_liv_souhait'],
    'date_commande' => $cmd['date_commande'],
    'statut_commande' => $cmd['statut_commande'],
    'statut_livraison' => $cmd['statut_livraison'],
    'date_livraison' => $cmd['date_livraison']
    ];
        }

        $grouped[$key]['articles'][] = [
            'image' => $cmd['image'],
            'nom_modele' => $cmd['nom_modele'],
            'quantite' => $cmd['quantite'],
            'taille' => $cmd['taille'],
            'tissu' => $cmd['tissu'],
            'personnalisation' => $cmd['personnalisation'],
            'prix' => $cmd['prix']
        ];
    }
?>

<?php foreach ($grouped as $commande): ?>
<div class="commande-block">
    <h3>Client : <?= htmlspecialchars($commande['client']['prenom'] . ' ' . $commande['client']['nom']) ?></h3>
    <p><strong>Commande N° :</strong> #<?= htmlspecialchars($commande['commande']['id_commande']) ?></p>
    <p><strong>Email :</strong> <?= htmlspecialchars($commande['client']['email']) ?></p>
    <p><strong>Téléphone :</strong> <?= htmlspecialchars($commande['client']['telephone']) ?></p>
    <p><strong>Lieu d'habitation :</strong> <?= htmlspecialchars($commande['client']['lieu_habitation']) ?></p>
    <p><strong>Mode de paiement :</strong> <?= htmlspecialchars($commande['commande']['mode_paiement']) ?></p>
    <p><strong>Date commande :</strong> <?= htmlspecialchars($commande['commande']['date_commande']) ?></p>
    <p><strong>Date livraison souhaitée :</strong> <?= htmlspecialchars($commande['commande']['date_liv_souhait']) ?></p>
        <p><strong>Date de livraison final :</strong> 
<?= empty($commande['commande']['date_livraison']) 
     ? "<span class='status en-attente' data-base='En attente' style='color:gray;'>En attente</span>  ⏳" 
     : htmlspecialchars($commande['commande']['date_livraison']) ?>
</p>


       <p><strong>Statut commande :</strong>
<?php
    $statut_commande = $commande['commande']['statut_commande'] ?? 'non défini';
    echo match($statut_commande) {
        'en attente' => "<span class='status en-attente' data-base='En attente'>En attente</span> ⏳",
        'en préparation' => "<span class='status en-preparation' data-base='En préparation'>En préparation</span>🧵",
        'prête' => "<span class='status en-livree'>Prête ✅</span>",
        default => "<span class='status unknown'>" . htmlspecialchars($statut_commande) . "</span>"
    };
?>
</p>

<p><strong>Statut livraison :</strong>
<?php 
    $statut_livraison = $commande['commande']['statut_livraison'] ?? '';
    echo match($statut_livraison) {
        'en route' => "<span class='status en-route' data-base='En route'>En route</span> 🚚",
        'récupération du colis' => "<span class='status en-retrait' data-base='Récupération du colis'>Récupération du colis</span>📦",
        'livrée' => "<span class='status en-livree'>Livrée ✅</span>",
        default => "<span class='status unknown' data-base='En attente'>En attente</span>⏳"
    };
?>
</p>

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
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php foreach ($commande['articles'] as $item): ?>
                <?php $sous_total = $item['quantite'] * $item['prix']; ?>
                <?php $total += $sous_total; ?>
                <tr>
                    <td>
                        <?php if (!empty($item['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Image" class="img-thumbnail">
                        <?php else: ?>
                            <span style="color:gray;">Aucune image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($item['nom_modele']) ?></td>
                    <td><img src="uploads/<?= htmlspecialchars($item['tissu']) ?>" alt="Tissu" class="img-thumbnail"></td>
                    <td><?= $item['quantite'] ?></td>
                    <td><?= htmlspecialchars($item['taille']) ?></td>
                    <td><?= htmlspecialchars($item['personnalisation']) ?></td>
                    <td><?= $item['prix'] ?> FCFA</td>
                    <td><?= $sous_total ?> FCFA</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total">Total : <?= $total ?> FCFA</p>

    <a onclick="return confirm('Voulez-vous vraiment supprimer cette commande ?')"
       href="supprimer_commande.php?id_commande=<?= $commande['commande']['id_commande'] ?>"
       class="btn-delete">
       Supprimer
    </a>
</div>
<?php endforeach; ?>

<?php else: ?>
    <p style="text-align:center; color:#a72872;">Aucune commande enregistrée pour le moment.</p>
<?php endif; ?>
</div>

<!-- Modale image -->
<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
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

    const animatedStatuses = document.querySelectorAll('.status[data-base]');

animatedStatuses.forEach(el => {
    const baseText = el.dataset.base;
    let dots = 0;

    setInterval(() => {
        dots = (dots + 1) % 4;
        el.textContent = baseText + '.'.repeat(dots);
    }, 600);
});

</script>

</body>
</html>
