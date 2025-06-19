<?php
    require("config.php");

    $requeteCouturiers = "SELECT * FROM couturier";
    $prepareCouturiers = $pdo->prepare($requeteCouturiers);
    $prepareCouturiers->execute();
    $couturiers = $prepareCouturiers->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des couturiers</title>
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
        thead {
            background-color: rgb(191, 89, 162);
            color: white;
        }
        h1{
            text-align: center;
            color: #a72872;
            font-size: 50px;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }

    </style>

</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="content">

    <h1>Nos couturiers</h1>

    
    <button onclick="window.location.href='ajoutcouturier.php'">
        <i class="fa-solid fa-user-plus"></i> 
    </button>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Email</th>
                    <th>Mot de passe</th>
                    <th>Téléphone</th>
                   <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($couturiers as $couturier): ?>
                <tr>
                    <td><?= $couturier["id_couturier"] ?></td>
                    <td><?= $couturier["nom"] ?></td>
                    <td><?= $couturier["prenom"] ?></td>
                    <td><?= $couturier["email"] ?></td>
                    <td><?= $couturier["mot_de_passe"] ?></td>
                    <td><?= $couturier["telephone"] ?></td>
                   <td><a href="modifcouturier.php?param=<?= $couturier["id_couturier"] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
                    
                    <td><a onclick="return confirm('Voulez-vous supprimer ce couturier ?');" href="supprcouturier.php?param=<?= $couturier["id_couturier"] ?>"><i class="fa-solid fa-trash"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


</div>
</body>
</html>
