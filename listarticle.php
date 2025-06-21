
<?php
    include("config.php");
    $requete="SELECT * FROM article";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute();
    $affiche=$prepare->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/brands.min.css" integrity="sha512-9YHSK59/rjvhtDcY/b+4rdnl0V4LPDWdkKceBl8ZLF5TB6745ml1AfluEU6dFWqwDw9lPvnauxFgpKvJqp7jiQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/bootstrap.css">
     <link rel="stylesheet" href="css/bootstrap.min.css">
    

     <style>

       /* Style pour les images */
    .img-thumbnail {
      width: 100px;
      height: auto;
      cursor: pointer;
    }

         .fa-solid{
            width:40px;
            color:rgb(167, 40, 131);
        }
  
        button{
            width:200px;
            border-radius:2px;
            background-color:rgb(228, 98, 189);
            font-size:25px;
            margin-top:20px;
            border:none;
        }

        thead{
            background-color: rgb(191, 89, 162) ;
            color:white;
        }
        
        p{
            text-align: center;
            color: #a72872;
            font-size:50px;
            font-family:Georgia, 'Times New Roman', Times, serif;
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
        height: auto;
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

    


        

     </style>

</head>
<body>
<?php include 'catmenu.php'; ?>

<div class="content">
  
<p >
        Nos vetements
    </p>


<button onclick="window.location.href='ajoutarticle.php'"> <i class="fa-solid fa-plus"></i> </button>
    
    
    <div class="table-responsive">
    <table class="table  table-striped table-hover " >
        <thead>
        <tr>
            <th>#</th>
            <th>Image</th>
            <th>Nom de modèle</th>
            <th>Catégorie personne</th>
            <th>description</th>
            <th>Prix</th>
            <th>Modifier</th>
            <th>Supprimer</th>
        </tr>
        </thead>
        
        <?php
            do{
        ?>
        <tbody>
        <tr>
            <td><?php echo $affiche["id_article"] ?></td>
            <td><a href="javascript:void(0)"><img src="uploads/<?= htmlspecialchars($affiche['image']) ?>" alt="Image Client" class="img-thumbnail" id="myImg"></td>
           
            <td><?php echo $affiche["nom_modele"] ?></td>
            <td><?php echo $affiche["categorie"] ?></td>
            <td><?php echo $affiche["description"] ?></td>
            <td><?php echo $affiche["prix"] ?></td>

            <td><a href="modifarticle.php?param=<?php echo $affiche["id_article"] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
            
            <td><a onclick="if(confirm('voulez vous supprimer ?')){}else{return false}" href="suppvet.php?param=<?php echo $affiche["id_article"] ?>"><i class="fa-solid fa-trash"></i></a></td>
            
        </tr>
        </tbody>
        
        <!-- onclick="if(confirm('voulez vous supprimer ?')){}else{return}" -->
        <?php }while($affiche=$prepare->fetch(PDO::FETCH_ASSOC)) ?>
    </table>
    </div>
    
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


</body>
</html>