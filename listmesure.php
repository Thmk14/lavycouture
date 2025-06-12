<?php
    require("config.php");
    $requete="SELECT m.*, c.nom, c.prenom
    FROM mensuration m
    JOIN client c ON m.id_client = c.id_client";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute();
    $affiche=$prepare->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/brands.min.css" integrity="sha512-9YHSK59/rjvhtDcY/b+4rdnl0V4LPDWdkKceBl8ZLF5TB6745ml1AfluEU6dFWqwDw9lPvnauxFgpKvJqp7jiQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link rel="stylesheet" href="css/bootstrap.css">
     <link rel="stylesheet" href="css/bootstrap.min.css">
    

    <title>Liste des mesures</title>
    <style>
        img{
            width:90px;
            height:90px;
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

        
    </style>
</head>
<body>
<?php include 'catmenu.php'; ?>

<div class="content">

<p>Liste des Mesures</p>


<button onclick="window.location.href='ajoutmesure.php'"> <i class="fa-solid fa-plus"></i> </button>
    
  

<?php if ($affiche): ?>
    <div class="table-responsive">
    <table class="table  table-striped table-hover " >
        <thead>
            <tr>
                <th>#</th>
                <th>Nom et prénom client</th>
                <th>Lieu de prise</th>
                <th>Tour de Taille</th>
                <th>Tour de Poitrine</th>
                <th>Tour de Hanche</th>
                <th>Taille du Buste</th>
                <th>Longueur du Bras</th>
                <th>Tour Bras</th>
                <th>Longueur de Jambe</th>
                <th>Tour de Cuisse</th>
                <th>Tour de Cou</th>
                <th>Largeur des Épaules</th>
                <th>Longueur de l'Entrejambe</th>
                <th>Longueur Totale</th>
                
                <th>Modifier</th>
                <th>Supprimer</th>
                
            </tr>
        </thead>
        <?php
            do{
        ?>
        <tbody>
            
                <tr>
                    <td><?php echo $affiche['id_mensuration'] ?></td>
                    <td><?php echo ($affiche['nom'] .' '. $affiche['prenom']  )?> </td>
                    <td><?php echo $affiche['lieu_prise'] ?> </td>
                    <td><?php echo $affiche['tour_taille'] ?> cm</td>
                    <td><?php echo $affiche['tour_poitrine'] ?> cm</td>
                    <td><?php echo $affiche['tour_hanche'] ?> cm</td>
                    <td><?php echo $affiche['taille_buste'] ?> cm</td>
                    <td><?php echo $affiche['longueur_bras'] ?> cm</td>
                    <td><?php echo $affiche['tour_bras'] ?> cm</td>
                    <td><?php echo $affiche['longueur_jambe'] ?> cm</td>
                    <td><?php echo $affiche['tour_cuisse'] ?> cm</td>
                    <td><?php echo $affiche['tour_cou'] ?> cm</td>
                    <td><?php echo $affiche['longueur_epaule'] ?> cm</td>
                    <td><?php echo $affiche['longueur_entrejambe'] ?> cm</td>
                    <td><?php echo $affiche['longueur_total'] ?> cm</td>
                    

                
                    <td><a href="modifmesure.php?param=<?php echo $affiche["id_mensuration"] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
                    <td><a onclick="if(confirm('voulez vous supprimer ?')){}else{return false}" href="suppmesure.php?param=<?php echo $affiche["id_mensuration"] ?>"><i class="fa-solid fa-trash"></i></a></td>
               </tr>
           
        </tbody>

        <!-- onclick="if(confirm('voulez vous supprimer ?')){}else{return}" -->
        <?php }while($affiche=$prepare->fetch(PDO::FETCH_ASSOC)) ?>
    </table>
<?php else: ?>
    <p style="text-align: center; font-size:20px; color: #666;">Aucune mesure enregistrée pour le moment.</p>
<?php endif; ?>
</div>

</body>
</html>
