<?php
require 'config.php';
require 'session.php';

// Vérifier si la session est déjà démarrée
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$id_client = $_SESSION['id'] ?? null;

if (!$id_client) {
    die("Erreur : ID client non défini.");
}

// Suppression de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_commande_id'])) {
    $commande_id = $_POST['delete_commande_id'];

    // Vérification du statut de la commande
    $stmt_status = $pdo->prepare("SELECT statut FROM commande WHERE id_commande = ?");
    $stmt_status->execute([$commande_id]);
    $status = $stmt_status->fetch(PDO::FETCH_ASSOC);

    if ($status && $status['statut'] === 'En attente') {
        try {
            $pdo->beginTransaction();

            // Suppression des articles liés à la commande
            $stmt = $pdo->prepare("DELETE FROM concerner WHERE id_commande = ?");
            $stmt->execute([$commande_id]);

            // Suppression de la commande
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
$sql = "SELECT c.*, art.*, cmd.*, cl.*, m.*
        FROM concerner c
        JOIN article art ON c.id_article = art.id_article
        JOIN commande cmd ON c.id_commande = cmd.id_commande
        JOIN client cl ON cmd.id_client = cl.id_client
        JOIN mensuration m ON cmd.id_mensuration = m.id_mensuration
        WHERE cmd.id_client = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_client]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Regroupement des commandes par client
$grouped = [];
foreach ($commandes as $cmd) {
    $key = $cmd['id_client'];
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'client' => [
                'nom' => $cmd['nom'],
                'prenom' => $cmd['prenom'],
                'email' => $cmd['email'],
                'telephone' => $cmd['telephone'],
                'ville' => $cmd['ville'],
                'pays' => $cmd['pays'],
                'lieu_habitation' => $cmd['lieu_habitation']
            ],
            'commandes' => []
        ];
    }

    $grouped[$key]['commandes'][] = [
        'id_commande' => $cmd['id_commande'],
        'mode_paiement' => $cmd['mode_paiement'],
        'montant_total' => $cmd['montant_total'],
        'date_commande' => $cmd['date_commande'],
        'statut' => $cmd['statut'],
        'date_livraison' => $cmd['date_livraison'],
        'articles' => [
            [
                'image' => $cmd['image'],
                'nom_modele' => $cmd['nom_modele'],
                'quantite' => $cmd['quantite'],
                'taille_standard' => $cmd['taille_standard'],
                'tissu' => $cmd['tissu'],
                'description_modele' => $cmd['description_modele'],
                'prix' => $cmd['prix']
            ]
        ]
    ];
}

?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
     
    <title>Commandes Clients - Admin</title>
    <style>

 /* ========= Global Styles ========= */
body {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color:rgb(254, 216, 241);
    color: #333;
}

h1 {
    text-align: center;
    color: #a72872;
    margin: 180px 0 30px;
    font-size: 2.5rem;
}

/* ========= Commande Block ========= */
.commande-block {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
    padding: 25px;
    margin: 30px 20px;
    transition: 0.3s;
}

.commande-block h2 {
    font-size: 1.5rem;
    color: #a72872;
    margin-bottom: 15px;
}

.commande-block p {
    margin: 8px 0;
    font-size: 1rem;
}

/* ========= Tables ========= */
table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    overflow-x: auto;
}

th, td {
    padding: 12px;
    border: 1px solid #f1d5e7;
    text-align: center;
}

th {
    background-color:rgb(184, 26, 128);
    color: #fff;
    font-weight: bold;
}

/* ========= Images ========= */
img {
    border-radius: 6px;
    width: 70px;
}

.img-thumbnail {
    width: 100px;
    height: auto;
    cursor: pointer;
}

/* ========= Buttons ========= */
.buttonn {
    display: inline-block;
    padding: 10px 20px;
    background-color: rgba(241, 98, 186, 0.75);
    color: black;
    text-decoration: none;
    border-radius: 6px;
    border: none;
    margin-top: 10px;
    cursor: pointer;
}

.disabled-button {
    background: rgba(81, 77, 79, 0.75);
    color: white;
    cursor: not-allowed;
}

.active-button {
    background-color: rgba(241, 98, 186, 0.75);
    color: black;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    background-color:rgb(234, 8, 185); /* Bleu bootstrap */
    border-radius: 5px;
    border: none;
    transition: background-color 0.3s ease;
    margin-left: 80px;
}

.btn:hover {
    background-color:rgb(179, 0, 134); /* Bleu plus foncé */
}

.btn a{
    text-decoration: none;
    color: white;
     font-weight: bold;
     font-size: 20px;
}

/* ========= Status Tags ========= */
.status {
    font-weight: bold;
    font-size: 0.95rem;
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

/* ========= Totals ========= */
.total {
    text-align: right;
    font-weight: bold;
    color: #a72872;
    margin-top: 10px;
}

.total-general {
    text-align: center;
    font-size: 1.5rem;
    font-weight: bold;
    background-color:rgb(254, 251, 253);
    border: 2px solid rgb(253, 208, 235);
    border-radius: 8px;
    color: #a72872;
    padding: 15px;
    margin: 40px 20px 80px 20px;
    box-shadow: 0 3px 8px rgba(114, 19, 75, 0.35);
}

/* ========= No Data ========= */
.no-data {
    font-size: 1.8rem;
    text-align: center;
    margin: 200px auto;
    color: #999;
}

/* ========= Forms ========= */
form {
    width: 100%;
    text-align: center;
    margin-bottom: 50px;
}

form button {
    width: 40%;
    padding: 12px 20px;
    margin-top: 40px;
    background-color: rgb(251, 107, 189);
    color: white;
    font-size: 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

/* ========= Modals ========= */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.9);
    overflow: auto;
    padding-top: 80px;
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    width: 600px;
}

.close {
    position: absolute;
    top: 30px;
    right: 45px;
    color: #f1f1f1;
    font-size: 36px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.close:hover {
    color: #bbb;
}



/* ========= Responsive ========= */
@media (max-width: 992px) {
    h1 {
        font-size: 2rem;
        margin: 180px 0 30px;
    }

    .commande-block {
        margin: 20px 10px;
        padding: 20px;
    }

    table {
        font-size: 0.9rem;
    }

    th, td {
        padding: 8px;
    }

    img, .img-thumbnail {
        width: 60px;
    }
}

@media (max-width: 576px) {
    h1 {
        font-size: 1.5rem;
        margin-top: 180px;
    }

    .buttonn {
        font-size: 0.9rem;
        padding: 8px 12px;
    }

    form button {
        width: 80%;
        font-size: 0.9rem;
    }

    .modal-content {
        width: 90%;
    }

    table {
        display: block;
        overflow-x: auto;
    }

    th, td {
        font-size: 0.75rem;
        white-space: nowrap;
    }

    img, .img-thumbnail {
        width: 50px;
    }
}

    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="main-content">


<?php
$total_general = 0;

if (count($commandes) > 0):
    foreach ($grouped as $client_id => $data): ?>
        <h1>Mes informations</h1>

        <button class="btn"><a href="historique" >Historique</a></button>
        <div class="commande-block">
            <p><strong>Nom et prénom :</strong> <?= htmlspecialchars($data['client']['nom'] . ' ' . $data['client']['prenom']) ?></p>
            
            <p><strong>Email :</strong> <?= htmlspecialchars($data['client']['email']) ?></p>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($data['client']['telephone']) ?></p>
            <p><strong>Pays :</strong> <?= htmlspecialchars($data['client']['pays']) ?></p>
            <p><strong>Ville :</strong> <?= htmlspecialchars($data['client']['ville']) ?></p>
            <p><strong>Lieu d'habitation :</strong> <?= htmlspecialchars($data['client']['lieu_habitation']) ?></p>
        </div>

        <?php foreach ($data['commandes'] as $commande): ?>
            <div class="commande-block">
                <h2>Commande #<?= htmlspecialchars($commande['id_commande']) ?></h2>
                <p><strong>Mode de paiement :</strong> <?= ($commande['mode_paiement']) ?></p>
                <p><strong>Date de la commande :</strong> <?= ($commande['date_commande']) ?></p>
                
    <p><strong>Statut commande :</strong>
<?php
$statut = strtolower($commande['statut'] ?? 'en attente');
echo match($statut) {
    'en route' => "<span class='status en-route' data-base='En route'>En route 🚚</span>",
    'récupération du colis' => "<span class='status en-retrait' data-base='Récupération du colis'>Récupération du colis 📦</span>",
    'livrée' => "<span class='status en-livree'>Livrée ✅</span>",
    'en attente' => "<span class='status en-attente' data-base='En attente'>En attente ⏳</span>",
    default => "<span class='status unknown'>Inconnu</span>"
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
                        <?php $total_commande = 0; ?>
                        <?php foreach ($commande['articles'] as $article): ?>
                            <?php $sous_total = $article['quantite'] * $article['prix']; ?>
                            <?php $total_commande += $sous_total; ?>
                            <tr>
                                <td><img src="uploads/<?= htmlspecialchars($article['image']) ?>" class="img-thumbnail"></td>
                                <td><?= htmlspecialchars($article['nom_modele']) ?></td>
                                <td><img src="uploads/<?= ($article['tissu']) ?>" class="img-thumbnail"></td>
                                <td><?= ($article['quantite']) ?></td>
                                <td><?= ($article['taille_standard']) ?></td>
                                <td><?= ($article['description_modele']) ?></td>
                                <td><?= ($article['prix']) ?> FCFA</td>
                                <td><?= ($sous_total) ?> FCFA</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!--p><strong>Total commande :</strong> <!?= number_format($total_commande, 0, ',', ' ') ?> FCFA</-p-->
                
                <?php $total_general += $total_commande; ?>

                <!-- Bouton supprimer -->
<?php if ($statut === 'en attente'): ?>
<form method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cette commande ?');">
    <input type="hidden" name="delete_commande_id" value="<?= htmlspecialchars($commande['id_commande']) ?>">
    <button type="submit" class="buttonn active-button">
        Supprimer la commande
    </button>
</form>
<?php endif; ?>

            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <div class="total-general">
        Montant total général : <?= number_format($total_general, 0, ',', ' ') ?> FCFA
    </div>

<?php else: ?>
    <div class="no-data">Aucune commande trouvée.</div>
    <form style="text-align:center;">
        <button type="button" onclick="window.location.href='catalogue.php'" style="padding: 10px 20px; margin:50px; background-color:rgb(251, 107, 189); border: none; border-radius: 5px; color: white; font-size: 16px; cursor: pointer;">
            Passer une commande
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



