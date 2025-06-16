<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require 'config.php';

$id_client = $_SESSION['id'];
        $query = "SELECT *
                  FROM concerner c
                  JOIN article art ON c.id_article = art.id_article
                  JOIN commande cmd ON c.id_commande = cmd.id_commande
                 JOIN mensuration m ON cmd.id_mensuration= m.id_mensuration
                  WHERE cmd.id_client = ? AND cmd.etat_commande = 0
  
                  ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_client]);
        $result = $stmt->fetchAll();

?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Mon Panier</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color:rgb(251, 193, 228);
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #a72872;
            font-size: 40px;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background-color: #a72872;
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

        
        .quantity-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 5px;
        }

        .quantity-container button {
            padding: 6px 10px;
            background-color:rgb(194, 52, 146);
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .quantity-container button:hover {
            background-color:rgb(252, 67, 175);
        }

        .quantity-container input {
            width: 50px;
            text-align: center;
            border: 1px solid #ccc;
            background-color: #fff;
            border-radius: 4px;
            font-weight: bold;
        }

        a.btn, i.btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #a72872;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            margin-top: 20px;
        }

        a.btn:hover {
            background-color: #8d205e;
        }




        @media screen and (max-width: 768px) {
     table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
       
    }


    thead {
        display: none; /* Cacher les titres des colonnes */
    }

    tr {
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
    }

    td {
        text-align: left;
        padding-left: 50%;
        position: relative;
        font-size: 14px;
    }

    td::after {
        position: absolute;
        top: 12px;
        left: 10px;
        width: 45%;
        white-space: nowrap;
        font-weight: bold;
        color: #a72872;
    }

    td:nth-of-type(1)::after{ content: "Image"; }
    td:nth-of-type(2)::after{ content: "Modèle"; }
    td:nth-of-type(3)::after{ content: "Type tissu"; }
    td:nth-of-type(4)::after{ content: "Quantité"; }
    td:nth-of-type(5)::after{ content: "Taille"; }
    td:nth-of-type(6)::after{ content: "Personnalisation"; }
    td:nth-of-type(7)::after{ content: "Prix"; }
    td:nth-of-type(8)::after{ content: "Total"; }


    .quantity-container {
        justify-content: flex-end;
    }

    h3#total-general {
        font-size: 18px;
    }
}

        
        td a i {
            color: #a72872;
            font-size: 30px;
        }

        .locked {
            opacity: 0.6;
            cursor: not-allowed;
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
      margin:  auto;
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
</head>
<body>

<h1>Mon Panier</h1>



<?php if (!$_SESSION['id'] || empty($result)): ?>
    <p style="text-align:center; color:#a72872; font-weight:bold;">Votre panier est vide.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>Taille</th>
            <th>Prix supplémentaire</th>
            <th>Description</th>
            <th>Tissu</th>
            <th>Mensurations</th>
            <th>Total</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        
          $total = 0;

        foreach ($result as $row) {
            
            $total = $row['prix'] * $row['quantite'];
            echo "<tr>";
            echo "<td><img src='uploads/{$row['image']}' style='height: 80px;'  alt='Image Client' class='img-thumbnail' id='myImg'></td>";
            echo "<td>{$row['nom_modele']}</td>";
            echo "<td>{$row['prix']} FCFA</td>";
            echo "<td>{$row['quantite']}</td>";
            echo "<td>" . (!empty($row['taille_standard']) ? $row['taille_standard'] : "Aucune") . "</td>";
            echo "<td>" . (!empty($row['supplement_prix']) ? $row['supplement_prix'] : "Aucun") . "</td>";

           

            // Personnalisation
            echo "<td>" . (!empty($row['description_modele']) ? $row['description_modele'] : "Aucune") . "</td>";

            echo "<td><img src='uploads/{".(!empty($row['tissu']) ? $row['tissu'] : "Aucun")." }' style='height: 80px;' alt='Image Client' class='img-thumbnail' id='myImg'> </td>";

        echo '<td><a href="listmesurec.php?id_commande=' . $row['id_commande'] . '" class="btn-mesures">Mensuration</a></td>';
  
             echo "<td>$total FCFA</td>";
           

            // Bouton supprimer
            echo "<td><a href='supprimer_panier.php?id={$row['id_concerner']}' onclick='return confirm(\"Supprimer cet article ?\")'><i class='fa-solid fa-trash'></i></a></td>";

            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<h3>Total général : <span id="total-general"><?= $total ?> FCFA</span></h3>
<?php endif; ?>

<?php if (!empty($result)): ?>
    <a href="catalogue.php" class="btn" >Continuer les achats </a>
    <a href="commande.php" class="btn" >Commander</a>
<?php else: ?>
    <a href="catalogue.php" class="btn">Voir le catalogue </a>
<?php endif; ?>






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


document.querySelectorAll("tr[data-id]").forEach(row => {
    const input = row.querySelector(".quantite");
    const plus = row.querySelector(".plus");
    const moins = row.querySelector(".moins");
    const idPanier = row.dataset.id;

    const updateQuantity = (newVal) => {
        fetch("update_quantite.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id_panier=${idPanier}&quantite=${newVal}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.sous_total) {
                row.querySelector(".sous-total").textContent = data.sous_total + " FCFA";

                let total = 0;
                document.querySelectorAll(".sous-total").forEach(td => {
                    total += parseInt(td.textContent);
                });
                document.getElementById("total-general").textContent = total + " FCFA";
            } else {
                alert("Erreur de mise à jour !");
            }
        });
    };

    if (plus) {
        plus.addEventListener("click", () => {
            if (input.hasAttribute("disabled")) return;
            input.value = parseInt(input.value) + 1;
            updateQuantity(input.value);
        });
    }

    if (moins) {
        moins.addEventListener("click", () => {
            if (input.hasAttribute("disabled")) return;
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
                updateQuantity(input.value);
            }
        });
    }

    input.addEventListener("change", () => {
        if (input.hasAttribute("disabled")) return;
        if (parseInt(input.value) < 1) input.value = 1;
        updateQuantity(input.value);
    });
});
</script>

</body>
</html>
