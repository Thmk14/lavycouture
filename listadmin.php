<?php
    require("config.php");

    $requeteAdmins = "SELECT * FROM administrateur";
    $prepareAdmins = $pdo->prepare($requeteAdmins);
    $prepareAdmins->execute();
    $admins = $prepareAdmins->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des administrateurs</title>
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

    <h1>Nos administateurs</h1>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénoms</th>
                    <th>Email</th>
                   
                    <th>Téléphone</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td><?= $admin["id_admin"] ?></td>
                    <td><?= $admin["nom"] ?></td>
                    <td><?= $admin["prenom"] ?></td>
                    <td><?= $admin["email"] ?></td>
                   
                    <td><?= $admin["telephone"] ?></td>
                    <td><a href="modifadmin.php?param=<?php echo $admin["id_admin"] ?>"><i class="fa-solid fa-pen-to-square"></i></a></td>
                    <td><a onclick="return confirm('Voulez-vous supprimer cet administrateur ?');" href="suppradmin.php?param=<?= $admin["id_admin"] ?>"><i class="fa-solid fa-trash"></i></a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>


</div>
</body>
</html>
