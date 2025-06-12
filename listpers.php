
<?php
    include("config.php");
    $requete="SELECT * FROM personnel";
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
  
<p >
        Les membres du personnel
    </p>


<button onclick="window.location.href='ajoutpers.php'"> <i class="fa-solid fa-user-plus"></i></button>
    
    
    <div class="table-responsive">
    <table class="table  table-striped table-hover " >
        <thead>
        <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Mot de passe</th>
            <th>Fonction</th>
            <th>Telephone</th>
            <th>Modifier</th>
            <th>Supprimer</th>
        </tr>
        </thead>
        
        <?php
            do{
        ?>
        <tbody>
        <tr>
            <td><?php echo $affiche["id_personnel"] ?></td>
            <td><?php echo $affiche["nom"] ?></td>
            <td><?php echo $affiche["email"] ?></td>
            <td><?php echo $affiche["mot_de_passe"] ?></td>
            <td><?php echo $affiche["fonction"] ?></td>
            <td><?php echo $affiche["telephone"] ?></td>
            <td><a href="modifpers.php?param=<?php echo $affiche["id_personnel"] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
            <td><a onclick="if(confirm('voulez vous supprimer ?')){}else{return false}" href="suppers.php?param=<?php echo $affiche["id_personnel"] ?>"><i class="fa-solid fa-trash"></i></a></td>
        </tr>
        </tbody>
        
        <!-- onclick="if(confirm('voulez vous supprimer ?')){}else{return}" -->
        <?php }while($affiche=$prepare->fetch(PDO::FETCH_ASSOC)) ?>
    </table>
    </div>
    
</div>
    
</body>
</html>