<?php
    require("config.php");

    //  Récupérer tous les clients
    $requeteClients = "SELECT * FROM client WHERE email IS NULL AND mot_de_passe IS NULL ";
    $prepareClients = $pdo->prepare($requeteClients);
    $prepareClients->execute();
    $clients = $prepareClients->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des clients de l'atelier</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <style>
        

        .fa-solid {
            width: 40px;
            color: rgb(167, 40, 131);
        }
        button {
            width: 200px;
            border-radius: 2px;
            background-color: rgb(228, 98, 189);
            font-size: 25px;
            margin-top: 20px;
            border: none;
        }

        .button {
            width: 200px;
            border-radius: 2px;
            background-color: rgb(228, 98, 189);
            font-size: 20px;
            margin-top: -20px;
            border: none;
        }

        thead {
            background-color: rgb(191, 89, 162);
            color: white;
        }
        h1 {
            text-align: center;
            color: #a72872;
            font-size: 50px;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }

    </style>

</head>
<body>

<?php include 'catmenuc.php'; ?>

<div class="content">

    <h1>Nos clients de l'atelier</h1>

    <button onclick="window.location.href='ajoutclientatelier.php'">
        <i class="fa-solid fa-user-plus"></i> 
    </button>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Téléphone</th>
                    <th>Lieu d'habitation</th>
                    <th>Informations commandes</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= $client["id_client"] ?></td>
                    <td><?= $client["nom"] ?></td>
                    <td><?= $client["prenom"] ?></td>
                    <td><?= $client["telephone"] ?></td>
                    <td><?= $client["lieu_habitation"] ?></td>
                    <td><a href="commande_atelier.php?param=<?= $client["id_client"] ?>"><button class="button">Commande +</button></a></td>
                    
                    <td><a href="modifclient.php?param=<?= $client["id_client"] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
                    
                    <td><a onclick="return confirm('Voulez-vous supprimer ce client ?');" href="supprclient.php?param=<?= $client["id_client"] ?>"><i class="fa-solid fa-trash"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


</div>
</body>
</html>
