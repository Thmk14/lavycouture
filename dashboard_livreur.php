<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

// Vérifie que l'utilisateur est connecté en tant que personnel
if (!isset($_SESSION['id'])) {
    header('Location: connexion.php');
    exit();
}

$id = $_SESSION['id'];

// Requête pour récupérer les commandes prêtes à livrer
$sql = "SELECT *
                  FROM concerner c
                  JOIN article art ON c.id_article = art.id_article
                  JOIN commande cmd ON c.id_commande = cmd.id_commande
                    JOIN client cl ON cmd.id_client = cl.id_client
                 JOIN mensuration m ON cmd.id_mensuration= m.id_mensuration
                  WHERE cmd.id_client = ? AND cmd.statut = 'prête'";

        
$stmt = $pdo->prepare($sql);
$stmt->execute([$id]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si le livreur marque la commande comme "livrée", met à jour la base de données
if (isset($_GET['id']) && isset($_GET['statut'])) {
    $id_commande = $_GET['id'];
    $statut = $_GET['statut'];

    if ($statut == 'livrée') {
        $update = $pdo->prepare("UPDATE commande SET statut = 'livrée' WHERE id_commande = ?");
        $update->execute([$id_commande]);

        // Redirige après la mise à jour
        header('Location: dashboard_livreur.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Livreur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
            background-color: #f9f9f9;
        }

        h1 {
            text-align: center;
            color: #a72872;
            font-size: 50px;
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

        .btn {
            padding: 8px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 13px;
            margin: 2px;
            color: white;
            display: inline-block;
        }

        .btn-attente { background-color: gray; }    
        .btn-route { background-color: orange; }         
        .btn-recuperation { background-color: #5bc0de; } 
        .btn-livree { background-color: #5cb85c; }       

        .btn:hover {
            opacity: 0.85;
        }

        .check {
            font-size: 18px;
            color: green;
            display: block;
            margin-top: 5px;
        }

        @media screen and (max-width: 768px) {
            table {
                font-size: 12px;
            }
            th, td {
                padding: 8px;
            }
            h1 {
                font-size: 30px;
            }
        }

        @media screen and (max-width: 480px) {
            table {
                display: block;
                overflow-x: auto;
            }
            th, td {36
                font-size: 10px;
            }
            h1 {
                font-size: 24px;
            }
        }

        .main-content {
            transition: margin-left 0.3s ease;
            margin-left: 0;
        }
        #check:checked ~ .main-content {
            margin-left: 250px;
        }
    </style>
</head>
<body>

<?php include 'catmenul.php'; ?>

<div class="main-content">

    <h1>Commandes prêtes à livrer</h1>

    <?php if (empty($commandes)): ?>
        <p style="text-align:center; color:gray;">Aucune commande prête pour le moment.</p>
    <?php else: ?>
        <div class="div">
            <table>
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Téléphone</th>
                        <th>Total</th>
                        <th>Date livraison souhaitée</th>
                        <th>Date Livraison finale</th>
                        <th>Statut Commande</th>
                        <th>Statut Livraison</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $cmd): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($cmd['id_commande']) ?></td>
                            <td><?= htmlspecialchars($cmd['prenom'] . ' ' . $cmd['nom']) ?></td>
                            <td><?= htmlspecialchars($cmd['lieu_habitation']) ?></td>
                            <td><?= htmlspecialchars($cmd['telephone']) ?></td>
                            <td><?= number_format($cmd['montant_total'], 0, ',', ' ') ?> FCFA</td>
                            <td><?= htmlspecialchars($cmd['date_livraison']) ?></td>
                            <td>
  <form method="POST" action="mise_date_liv.php" style="display:flex; align-items:center; justify-content:center; gap:5px;">
    <input type="hidden" name="id_commande" value="<?= $cmd['id_commande'] ?>">
    <input 
      type="date" 
      name="date_livraison" 
      value="<?= htmlspecialchars($commande['date_livraison'] ?? '') ?>"
      required 
      style="padding:4px; font-size:14px;"
    >
    <input type="text" name="" value="<?= htmlspecialchars($cmd['date_livraison'] ?? '') ?>"  id="<?= htmlspecialchars($commande['id_commande'] ?? '') ?>">
    <button type="submit" style="padding:5px 8px; font-size:14px; background:#a72872; color:#fff; border:none; border-radius:4px; cursor:pointer;">
      Modifier
    </button>
  </form>
</td>

                            <td><?= htmlspecialchars($cmd['statut']) ?></td>
                            <td><?= htmlspecialchars($cmd['statut'] ?? 'en attente') ?></td>
                            <td>
                                <a class="btn btn-attente" href="valider_livraison.php?id=<?= $cmd['id_commande'] ?>&statut=en%20attente">En attente</a>
                                <a class="btn btn-recuperation" href="valider_livraison.php?id=<?= $cmd['id_commande'] ?>&statut=récupération%20du%20colis">Récupération</a>
                                
                                <a class="btn btn-route" href="valider_livraison.php?id=<?= $cmd['id_commande'] ?>&statut=en%20route">En route</a>
                                <a class="btn btn-livree" href="valider_livraison.php?id=<?= $cmd['id_commande'] ?>&statut=livrée">Livrée</a>
                                
                                <?php if (($cmd['statut'] ?? '') === 'livrée'): ?>
                                    <span class="check">✅ Livrée</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
