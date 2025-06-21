<?php 
    include ("config.php");
    $para=$_GET["param"];
    

    if(isset($_POST["nom"])){
       
        $nom=$_POST["nom"];
        $prenoms=$_POST["prenoms"];
       
        $telephone=$_POST["tel"];
        
        $requete="UPDATE livreur
                    SET nom=?,
                        prenom=?,
                       
                        telephone=?
                    WHERE id_livreur=?";
        $prepare=$pdo->prepare($requete);
        $tab=[$nom,$prenoms,$telephone,$para];
        $execute=$prepare->execute($tab);

         // Redirection après la modification
         if ($execute) {
            header("Location: listlivreur.php");
            exit(); 
        }
       
    }
    $requete="SELECT*FROM livreur WHERE id_livreur=?";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute([$para]);
    $affiche=$prepare->fetch(PDO::FETCH_ASSOC);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification informations livreur</title>
    <link rel="stylesheet" href="css/bootstrap.css">
     <link rel="stylesheet" href="css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="css/connexion.css">
    
</head>
<body>


        
<?php include 'catmenu.php'; ?>


<div class="form-box active" > 
    <form action="" method="post">
    <h2 class="h2" >Modification</h2>
                <label>Nom</label>
                <input name="nom" type="text"  value="<?php echo $affiche["nom"] ?>">
                <label>Prenoms</label>
                <input name="prenoms" type="text"  value="<?php echo $affiche["prenom"] ?>">
                <label>Telephone</label>
                <input type="tel" name="tel" id=""   value="<?php echo $affiche["telephone"] ?>">
            
                <button type="submit" name="register">Modifier</button>       
    </form>
</div>



</body>
</html>