<?php 

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
require 'config.php';
;
if (!isset($_SESSION['id_personnel'])) {
  header('Location: personnelog.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="css/catalogue.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
  
</head>
<body>
<?php include 'catmenuc.php'; ?>


  <!-- CONTENU PRINCIPAL -->
  <div class="content">
    <h2>Bienvenue sur votre tableau de bord cher couturier </h2>

    <div class="button-container">
     <a href="listclientc.php"><button>Liste des clients</button></a>
      <a href="listcomc.php"><button>Liste des commandes</button></a>
      <a href="note_mesurec.php"><button>Liste des mesures</button></a>
      <a href="listcreatec.php"><button>Liste des créations de nos clients</button></a>
      
    </div>
  </div>

</body>
</html>
