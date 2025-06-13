
<?php
require 'config.php';
require 'session.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$id_client = $_SESSION['id'] ?? null;

if (!$id_client) {
    die("Erreur : ID client non défini.");
}

// Suppression de la commande et du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_commande_id'])) {
    $commande_id = $_POST['delete_commande_id'];

    // Vérification du statut de la commande et de la livraison
    $stmt_status = $pdo->prepare("
        SELECT cmd.statut_commande, liv.statut_livraison 
        FROM commande cmd
        LEFT JOIN livraison liv ON cmd.id_commande = liv.id_commande
        WHERE cmd.id_commande = ?
    ");
    $stmt_status->execute([$commande_id]);
    $status = $stmt_status->fetch(PDO::FETCH_ASSOC);

    // Vérification si la commande peut être supprimée
    if ($status && $status['statut_commande'] === 'en attente') {
        try {
            $pdo->beginTransaction();

            // Supprimer les articles liés à la commande
            $stmt = $pdo->prepare("DELETE FROM commande WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            // Supprimer la livraison liée si elle existe
            $stmt = $pdo->prepare("DELETE FROM livraison WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            // Supprimer la commande elle-même
            $stmt = $pdo->prepare("DELETE FROM commande WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            $pdo->commit();
            header("Location: detail_commande.php?deleted=1");
            exit();
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Erreur lors de la suppression : " . $e->getMessage());
        }
    } else {
        $_SESSION['message_error'] = "Impossible de supprimer cette commande (statut non autorisé).";
        echo "<script>alert('Impossible de supprimer cette commande (statut non autorisé).'); window.location.href='detail_commande.php';</script>";
        exit();
    }
}

// Récupération des données du client et des commandes
$sql = " SELECT DISTINCT c.nom, c.prenom, c.email, c.telephone,c.ville, c.pays, c.lieu_habitation,
        cmd.mode_paiement, cmd.statut_commande, cmd.id_commande, cmd.date_commande,cmd.montant_total, 
        lca.*, liv.statut_livraison, liv.date_livraison,
        art.image AS image, art.nom_modele AS nom_modele, v.prix AS prix
    FROM commande cmd   
    JOIN client c ON cmd.id_client = c.id_client
    LEFT JOIN lien_commande_article lca ON lca.id_client = c.id_client  
    LEFT JOIN article art ON art.id_article = lca.id_article
    LEFT JOIN livraison liv ON liv.id_livraison = cmd.id_livraison
    WHERE c.id_client = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_client]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     
    <title>Commandes Clients - Admin</title>
    <style>

        /* Styles généraux */
body {
    
    margin: 0;
    padding: 0;
}
  
        h1 {
            text-align: center;
            color: #a72872;

            font-size: 40px;
            font-family:Georgia, 'Times New Roman', Times, serif;
        }
        .commande-block {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 200px 50px;
            
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
            color:rgba(167, 40, 114, 0.99);
            margin-top: 10px;
        }
        .buttonn {
            display: inline-block;
            padding: 10px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            border:none;
        }

       
    .disabled-button {
        background: rgba(81, 77, 79, 0.75);
        color: white;
        cursor: not-allowed;
    }
    .active-button {
        background-color:rgba(241, 98, 186, 0.75);
        color: black;
    }
            
    .no-data {
    font-size: 30px;
    margin: 200px auto;
    padding: 10px;
    text-align: center;
}

.no-data,
form {
    width: 100%;
    margin-bottom:50px;
}

form button {
    width: 30%;
    margin: 80px auto;
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


        
/* Responsive styles */
@media screen and (max-width: 768px) {
    .commande-block {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin: 200px 0 ;
            
        }

        
            
        .no-data {
    font-size: 30px;
    margin: 200px auto;
    padding: 10px;
    text-align: center;
}

.no-data,
form {
    width: 100%;
    margin-bottom:50px;
}

form button {
    width: 40%;
    margin: 80px auto;
}

        

    table {
        font-size: 12px;
    }

    th, td {
        padding: 8px;
    }

    img {
        width: 50px;
    }

    h1 {
        font-size: 20px;
    }
}

@media screen and (max-width: 480px) {
    table {
        display: block;
        overflow-x: auto;
    }

    th, td {
        font-size: 10px;
    }

    img {
        width: 40px;
    }

    h1 {
        font-size: 18px;
        margin-bottom: 30px;
    }

    .buttonn {
        
        padding: 5px;
        font-size: 12px;
    }



     /* Le style de la fenêtre modale */
  .modal {
      display: none; /* Cacher par défaut */
      position: fixed;
      z-index: 1;
      padding-top: 100px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgb(0,0,0); /* Black */
      background-color: rgba(0,0,0,0.9); /* Black with opacity */
    }

    /* Contenu de l'image dans la fenêtre modale */
    .modal-content {
      margin: auto;
      display: block;
      width: 50%;
      max-width: 500px;
    }

    
form button {
    width: 80%;
    margin: 80px auto;
}

    
}

        .main-content {
    transition: margin-left 0.3s ease;
    margin-left: 0;
}

#check:checked ~ .main-content {
    margin-left: 250px; /* même largeur que la sidebar */
}

  /* Le style de la fenêtre modale */
  .modal {
      display: none; /* Cacher par défaut */
      position: fixed;
      z-index: 1;
      margin-top: 120px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgb(0,0,0); /* Black */
      background-color: rgba(0,0,0,0.9); /* Black with opacity */
    }

    /* Contenu de l'image dans la fenêtre modale */
    .modal-content {
      margin: 100px auto;
      display: block;
      width: 100%;
      max-width: 600px;
    }

    /* Le bouton pour fermer la modale */
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

    /* Style pour les images */
    .img-thumbnail {
      width: 100px;
      height: auto;
      cursor: pointer;
    }
    </style>
</head>
<body>
    
<?php include 'menu.php'; ?>

<div class="main-content">


<?php
if (count($commandes) > 0):
    // Regrouper les lignes par client et commande
    $grouped = [];
    foreach ($commandes as $cmd) {
        $key = $cmd['id_client'] . '_' . $cmd['id_commande'];
        $grouped[$key]['client'] = [
            'nom' => $cmd['nom'],
            'prenom' => $cmd['prenom'],
            'email' => $cmd['email'],
            'telephone' => $cmd['telephone'],
            'ville' => $cmd['ville'],
            'pays' => $cmd['pays'],
            'lieu_habitation' => $cmd['lieu_habitation']
        ];
       $grouped[$key]['commande'] = [
    'id_commande' => $cmd['id_commande'],
    'mode_paiement' => $cmd['mode_paiement'],
    'montant_total' => $cmd['montant_total'],
    'date_commande' => $cmd['date_commande'],
    'statut_commande' => $cmd['statut_commande'],
    'statut_livraison' => $cmd['statut_livraison'],
    'date_livraison' => $cmd['date_livraison']
];

        $grouped[$key]['articles'][] = [
            'image' => $cmd['image'],
            'nom_modele' => $cmd['nom_modele'],
            'quantite' => $cmd['quantite'],
            'taille_standard' => $cmd['taille_standard'],
            'tissu' => $cmd['tissu'],
            'personnalisation' => $cmd['personnalisation'],
            'prix' => $cmd['prix']
        ];
    }
?>

<?php foreach ($grouped as $commande): ?>
    <div class="commande-block">
    <h1>Mes commandes </h1>
        <p><strong>Commande N° :</strong> #<?= htmlspecialchars($commande['commande']['id_commande']) ?></p>
        <p><strong>Nom et prénom :</strong> <?= htmlspecialchars($commande['client']['nom'] . ' ' . $commande['client']['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($commande['client']['email']) ?></p>
        <p><strong>Téléphone :</strong> <?= htmlspecialchars($commande['client']['telephone']) ?></p>
        <p><strong>Pays :</strong> <?= htmlspecialchars($commande['client']['pays']) ?></p>
        <p><strong>Ville :</strong> <?= htmlspecialchars($commande['client']['ville']) ?></p>
        <p><strong>Lieu d'habitation :</strong> <?= htmlspecialchars($commande['client']['lieu_habitation']) ?></p>
         <p><strong>Mode de paiement :</strong> <?= htmlspecialchars($commande['commande']['mode_paiement']) ?></p>
        
        <p><strong>Date de la commande :</strong> <?= htmlspecialchars($commande['commande']['date_commande']) ?></p>
        
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

      <p><strong>Date de livraison final :</strong> 
<?= empty($commande['commande']['date_livraison'])
     ? "<span class='status en-attente' data-base='En attente' style='color:gray;'>En attente</span>  ⏳" 
     : htmlspecialchars($commande['commande']['date_livraison']) ?>
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




        <?php endforeach; ?>
        
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
                <?php foreach ($commandes as $item): ?>
                    <?php $sous_total = $item['quantite'] * $item['prix']; ?>
                    <?php $total += $sous_total; ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['image'])): ?>
                                <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="Image Client" class="img-thumbnail" id="myImg">
                            
                                <?php else: ?>
                                <span style="color:gray;">Aucune image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item['nom_modele']) ?></td>
                        <td><img src="uploads/<?= htmlspecialchars($item['tissu']) ?>" alt="Image Client" class="img-thumbnail" id="myImg">
                        </td>
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

        
<div>
     <?php
        require 'config.php'; 
        if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
        
        $id_client = $_SESSION['id_client'] ?? null;

        if ($id_client) {
            $stmt = $pdo->prepare("
                SELECT commande.id_commande, commande.statut_commande, livraison.statut_livraison 
                FROM commande 
                LEFT JOIN livraison ON commande.id_commande = livraison.id_commande 
                WHERE commande.id_client = ?
            ");
            $stmt->execute([$id_client]);
            $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($commandes as $cmd) {
                $disable_button = ($cmd['statut_commande'] !== 'en attente' && $cmd['statut_livraison'] !== 'livrée');
                ?>

                <?php if ($disable_button): ?>
    <p style="color: red;"> Vous pourrez supprimer cette commande uniquement une fois qu'elle sera livrée.</p>
<?php endif; ?>

<form method="POST" action="supprimer_commande.php" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette commande ?');">
    <input type="hidden" name="delete_commande_id" value="<?= htmlspecialchars($cmd['id_commande']) ?>">
    <button type="submit" class="buttonn <?= $disable_button ? 'disabled-button' : 'active-button' ?>" <?= $disable_button ? 'disabled' : '' ?>>Supprimer la commande pour passer une nouvelle commande</button>
</form>

                <?php
            }
        } 
        ?>
</div>


    </div>


<?php else: ?>
    <div class="no-data">Aucune commande trouvée.</div>

    <form  style="text-align:center;">
    <button type="button" onclick="window.location.href='catalogue.php'" style="padding: 10px 20px; margin:50px; background-color:rgb(251, 107, 189); border: none; border-radius: 5px; color: white; font-size: 16px; cursor: pointer;">
            Passer une  commande
        </button>
        </form>
<?php endif; ?>
</div>


<!-- La modale -->
<div id="myModal" class="modal">
  <span class="close">&times;</span>
  <img class="modal-content" id="img01">
</div>

<script>
  // Obtenez la modal
var modal = document.getElementById("myModal");

// Obtenez le conteneur d'image modale
var modalImg = document.getElementById("img01");

// Trouver toutes les images avec la classe 'img-thumbnail'
document.querySelectorAll(".img-thumbnail").forEach(function(img) {
    img.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.src; // Charger l'image dans la modale
    };
});

// Quand l'utilisateur clique sur le bouton de fermeture, fermez la modale
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
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


<?php include 'footer.php'; ?>

</body>
</html>



