<?php 

session_start();
require 'config.php';

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
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="css/catalogue.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
  
</head>
<body>
<?php include 'catmenu.php'; ?>

  <!-- CONTENU PRINCIPAL -->
  <div class="content">
    <h2>Bienvenue sur votre tableau de bord cher administrateur </h2>

    <div class="button-container">
      <a href="liste_admin_commande.php"><button>Liste des commandes</button></a>
      <a href="listarticle.php"><button>Liste des vêtements</button></a>
      <a href="listclient.php"><button>Liste clients</button></a>
      <a href="listcouturier.php"><button>Liste couturiers</button></a>
      <a href="listlivreur.php"><button>Liste livreurs</button></a>
      <a href="listarticle.php"><button>Liste vêtements</button></a>
      
    </div>
  </div>

</body>
</html>
