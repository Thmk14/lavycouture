
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="css/catalogue.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: Arial, sans-serif;
      background-color:rgb(251, 199, 240);
    }

    #check {
      display: none;
    }

    label #btn, label #cancel {
      position: absolute;
      background: #fb6fdd;
      border-radius: 3px;
      cursor: pointer;
      z-index: 1001;
    }

    label #btn {
      left: 20px;
      top: 25px;
      font-size: 30px;
      color: white;
      padding: 8px 12px;
      transition: all .5s;
    }

    label #cancel {
      left: -50px;
      top: 25px;
      font-size: 30px;
      color: rgb(252, 179, 240);
      transition: all .5s ease;
    }

    .sidebar {
      position: fixed;
      left: -250px;
      width: 250px;
      height: 100%;
      background: #faaddb;
      transition: all .5s ease;
      top: 0;
      z-index: 1000;
      
    }

    .sidebar header {
      font-size: 22px;
      color: white;
      line-height: 70px;
      text-align: center;
      background:rgb(196, 51, 160);
      user-select: none;
    }

    .sidebar ul .active{
      color: white;
      font-size:25px;
      font-family:fantasy;
      background-color:rgb(230, 121, 203);
    }

    .sidebar ul a {
      display: block;
      padding-left: 40px;
      box-sizing: border-box;
      font-size: 18px;
      line-height: 45px;
      color:rgb(108, 19, 80);
      text-decoration: none;
    }

    .sidebar ul a:hover {
      padding-left: 50px;
      background-color:rgb(255, 189, 234);
    }


    .sidebar .logout {
      text-align: center;
      margin-top: 20px;
    }

    .sidebar .logout a {
      background: #fc42cd;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }

    #check:checked ~ .sidebar {
      left: 0;
    }

    #check:checked ~ label #btn {
      left: 250px;
      opacity: 0;
      pointer-events: none;
    }

    #check:checked ~ label #cancel {
      left: 210px;
    }

    #check:checked ~ .content {
      margin-left: 270px;
    }

    .content {
      margin: 0px 80px 0px 80px;
      padding: 70px 20px 20px 20px;
      transition: all .5s;
      
    }

    .content h2 {
      margin-bottom: 50px;
      color: #333;
      text-align:center;
      font-size:40px;
    }

    .button-container {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .button-container button {
      padding: 20px;
      font-size: 18px;
      width: 250px;
      height: 100px;
      background-color: rgb(167, 40, 131);
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background-color .3s;
    }

    .button-container button:hover {
      background-color: rgb(136, 33, 112);
    }

    .button-container button a {
      color: white;
      text-decoration: none;
      display: block;
      width: 100%;
      height: 100%;
    }
  </style>
</head>
<body>

  <input type="checkbox" id="check">
  <label for="check">
    <i class="fas fa-bars" id="btn"></i>
    <i class="fa-solid fa-square-xmark" id="cancel"></i>
  </label>

  <!-- SIDEBAR -->
  <div class="sidebar">
    <header>Lavit couture</header>

    <ul>
      <a href="admin.php" class="active"><li>Tableau de bord Administrateur</li></a>
      
      <a href="profil_personnel.php"><li>Mon profil</li></a>
      <a href="liste_admin_commande.php"><li>Commandes</li></a>
      <a href="listclient.php"><li>Nos clients</li></a>
      <a href="listcouturier.php"><li>Nos couturiers</li></a>
      <a href="listlivreur.php"><li>Nos livreurs</li></a>
      <a href="listarticle.php"><li>Nos vêtements</li></a>
    </ul>

    <div class="logout">
      <a href="deconnexion.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
  </div>

  
  </body>
  </html>