<?php
require 'config.php';
require 'session.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Vérifie que le client est connecté
if (!isset($_SESSION['id'])) {
    echo "<p style='color:red;'>❌ Vous devez être connecté pour passer une commande.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tel = $_POST['tel'];
    $mode = $_POST['mode'];
    $statut_com = $_POST['stat_com'] ?? 'En attente'; // Valeur par défaut si non définie
    $montant_total = $_POST['montant'] ?? 0; // Valeur par défaut si non définie
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];
    $lieu = $_POST['lieu'];
    $date = $_POST['date'];
    $dateLivraison = $_POST['dateliv'];

    try {
        $pdo->beginTransaction();

        $idClient = $_SESSION['id'];

        // Mise à jour de l'adresse et téléphone
        $sqlUpdate = "UPDATE client SET pays = ?, ville = ?, lieu_habitation = ?, telephone = ? WHERE id_client = ?";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([$pays, $ville, $lieu, $tel, $idClient]);

        // Insertion de la commande
        $sqlCommande = "INSERT INTO commande (mode_paiement, date_commande,date_liv, statut_commande, montant_total) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sqlCommande);
        $stmt->execute([$mode, $date,  $dateLivraison, $statut_com, $montant_total]);

        // Récupération de l'ID de la commande insérée
        $idCommande = $pdo->lastInsertId();

        // Insertion dans la table de liaison
        $sqlLien = "INSERT INTO commande (id_client, id_commande) VALUES (?, ?)";
        $stmt = $pdo->prepare($sqlLien);
        $stmt->execute([$idClient, $idCommande]);

        $pdo->commit();

        echo "<script>alert('✅ Commande enregistrée avec succès !'); window.location.href='detail_commande.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>❌ Une erreur est survenue : " . $e->getMessage() . "</p>";
    }
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valider commandes</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(253, 199, 237);
            margin: 0;
            padding: 0;
        }

        .form-container {
            width: 100%;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #555;
        }
        
        select,
        input[type="email"],
         input[type="number"],
         input[type="date"] ,
        input[type="text"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 50px ;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 16px;
            background-color: #fce3f3;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #a72872;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 40px;
        }

        button:hover {
            background-color: #882162;
        }

        @media (max-width: 768px) {
            .form-container {
                width: 95%;
            }
        }


        h2 {
            text-align: center;
            color: #a72872;
            font-size: 32px;
            margin-bottom: 30px;
        }

        
        th {
            background-color:rgb(240, 180, 215);
            color: #fff;
            padding: 15px;
            text-align: center;
            font-size: 18px;
        }

        td {
            padding: 15px;
            text-align: center;
            vertical-align: middle;
            font-size: 16px;
            color: #333;
        }

        img {
            border-radius: 8px;
            width: 80px;
            height: auto;
        }

        .quantity-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        
        .quantity-container input {
            width: 50px;
            text-align: center;
            border: 1px solid #ccc;
            background-color: #fff;
            border-radius: 4px;
            font-weight: bold;
        }


        @media screen and (max-width: 1024px) {
           
            table {
                display: block;
                overflow-x: ;
            }
            th, td {
                font-size: 10px;
            }
            h1 {
                font-size: 24px;
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
           h1 {
               font-size: 24px;
           }
       }

        td a i{
            color: #a72872;
            font-size: 30px;
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
      width: 100%;
      max-width: 500px;
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
    <script src="https://cdn.kkiapay.me/k.js"></script>
</head>
<body>




<div class="form-container">
   

    <h1>Confirmer la commande</h1>
   <form action="commande.php" method="POST" class="formulaire" id="formulaire-commande">

    <label for="tel">Téléphone</label>
    <input type="text" id="tel" name="tel" placeholder="Téléphone" required>

    <label for="date">Date du jour</label>
    <input type="date" id="date" name="date" required>

    
    <label for="mode">Mode de paiement</label>
    <select name="mode" id="mode" required>
        <option value="">-- Option de paiement --</option>
        <option value="Paiement à la livraison">Paiement à la livraison</option>
        <option value="Paiement par mobile money">Paiement par mobile money</option>
    </select>


    <label for="mode">Pays</label>
    <select name="mode" id="mode" required>
        <option value="">-- Pays --</option>
        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
    </select>

    <label for="mode">Ville</label>
    <select name="mode" id="mode" required>
        <option value="">-- Ville --</option>
        <option value="Paiement à la livraison">Abidjan</option>
        <option value="Paiement par mobile money">Yamoussoukro</option>
    </select>



    <label for="lieu">Votre lieu d'habitation</label>
    <input type="text" id="lieu" name="lieu" placeholder="Lieu d'habitation" required>

   
        <?php $id_client = $_SESSION['id'];
        $query = "SELECT *
                  FROM commande cmd
                  JOIN article art ON .id_article = art.id_article
                 JOIN mensuration m ON lca.id_mensuration= m.id_mensuration
                  WHERE lca.id_client = ?
  
                  ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_client]);
        $articles = $stmt->fetchAll();
          $total = 0;
 ?>
<table>
    <thead>
    <tr>
        <th>Image</th>
        <th>Modèle</th>
        <th>Type tissu</th>
        <th>Quantité</th>
        <th>Taille</th>
        <th>Personnalisation</th>
        <th>Prix supplémentaire</th>
        <th>Prix</th>
        <th>Total</th>
       
    </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    <?php foreach ($articles as $item): ?>
        <?php $sous_total = $item['quantite'] * $item['prix']; ?>
        <?php $total += $sous_total; ?>
        <tr data-id="<?= $item['id_lca'] ?>">
        <td><a href="javascript:void(0)"><img src="uploads/<?= htmlspecialchars($item['image'] ?? '') ?>" alt="Image Client" class="img-thumbnail" id="myImg"></td>
             
            <td><?= $item['nom_modele'] ?></td>
            <td><a href="javascript:void(0)"><img src="uploads/<?= htmlspecialchars(!empty($item['tissu']) ? $item['tissu'] : "Aucun") ?>" alt="Image Client" class="img-thumbnail" id="myImg"></td>
           
           

            <td><?= $item['quantite'] ?></td>
            <td><?= htmlspecialchars(!empty($item['taille_standard']) ? $item['taille_standard'] : "Aucun") ?></td>
            <td><?= htmlspecialchars(!empty($item['description_modele']) ? $item['description_modele'] : "Aucune") ?></td>
             <td><?= htmlspecialchars(!empty($item['supplement_prix']) ? $item['supplement_prix'] : "Aucune") ?></td>
            <td><?= $item['prix'] ?> FCFA</td>
            <td class="sous-total"><?= $sous_total ?> FCFA</td>
         </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Total général : <span id="total-general"><?= $total ?> FCFA</span></h3>


    <div id="section-paiement" style="display:none;">
        <label for="montant">Montant à payer (FCFA)</label>
        <input type="number" name="montant" id="montant">
        <button type="button" id="btn-payer" class="btn-payer">Payer maintenant</button>
    </div>


<button type="submit" name="btn-soumettre" id="btn-soumettre">Valider la commande</button>

    </form>
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

</script>
<script src="js/main.js"></script>




</body>


</html>
