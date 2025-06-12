<?php require 'config.php'; ?>
<?php
require 'session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  
    <link rel="stylesheet" href="css/apropos.css">
    
</head>
<body>

<?php include 'menu.php'; ?>

    <div class="about">
       
        <div class="left">
            <h3>A <span>propos</span></h3>
            <p>LAVY COUTURE est une entreprise qui confectionne des vêtements féminins africains à des prix adequats. 
                N'hésitez pas a nous ecrire pour profiter de nos offres!
            </p>
            <p>LAVY COUTURE : Votre style, notre passion.</p>
            </div>

        

        <div class="right">
            <div class="icon">
              <div class="pro">
              <i class="fa-solid fa-bolt"></i>
                <h4 class="h4">Rapide</h4>  
              </div>  
            </div>
            
            
            <div class="icon">
                <div class="pro">
                   <i class="fa-solid fa-scissors"></i>
                    <h4 class="h4">Précis</h4>
                </div>   
            </div>
            <div class="icon">
                <div class="pro">
                    <i class="fa-solid fa-circle-check"></i>
                    <h4 class="h4">Fiable</h4>
                </div>
            </div> 
        </div>        
    </div>
     
    <?php include 'footer.php'; ?>
    
</body>
</html>
