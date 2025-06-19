<?php 

if (session_status() == PHP_SESSION_NONE) {
  session_start();
}
require 'config.php';
;
if (!isset($_SESSION['id'])) {
  header('Location: connexion.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord de couturier</title>
  <link rel="stylesheet" href="css/catalogue.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
  
</head>
<body>
<?php include 'catmenuc.php'; ?>


  <!-- CONTENU PRINCIPAL -->
  <div class="content">
    <h2>Bienvenue sur votre tableau de bord cher couturier </h2>

    <div class="button-container">
      <a href="liste_couturier_commande.php"><button>Liste des commandes</button></a>
      <a href="client_atelier.php"><button>Liste de nos clients de l'atelier</button></a>
      <a href="listcreatec.php"><button>Liste des créations de nos clients</button></a>
      
    </div>
  </div>

</body>
</html>
