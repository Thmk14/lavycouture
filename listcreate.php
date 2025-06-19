<?php
    include("config.php");
    $requete = "SELECT * FROM proposition_modele";
    $prepare = $pdo->prepare($requete);
    $prepare->execute();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Les créations de nos clients</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Bootstrap -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <style>
        img {
            width: 90px;
            height: 90px;
        }

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

        p {
            text-align: center;
            color: #a72872;
            font-size: 50px;
            font-family: Georgia, 'Times New Roman', Times, serif;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }

        .modal-content {
            margin: auto;
            display: block;
            width: 100%;
            max-width: 500px;
            height: auto;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }

        .img-thumbnail {
            width: 100px;
            height: auto;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include 'catmenuc.php'; ?>

<div class="content">
    <p>Les créations de nos clients</p>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type de vêtement</th>
                    <th>Tissu</th>
                    <th>Longueur des manches</th>
                    <th>Coupe</th>
                    <th>Type de col</th>
                    <th>Existant</th>
                    <th>Description</th>
                    <th>Modifier</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php while($affiche = $prepare->fetch(PDO::FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($affiche["id_proposition"]); ?></td>
                    <td><?php echo htmlspecialchars($affiche["type_vetement"]); ?></td>
                    <td><?php echo htmlspecialchars($affiche["type_tissu"]); ?></td>
                    <td><?php echo htmlspecialchars($affiche["longueur_manche"]); ?></td>
                    <td><?php echo htmlspecialchars($affiche["coupe"]); ?></td>
                    <td><?php echo htmlspecialchars($affiche["type_col"]); ?></td>
                    <td>
                        <a href="javascript:void(0)">
                            <img src="uploads/<?php echo htmlspecialchars($affiche['existant']); ?>" alt="Image Client" class="img-thumbnail">
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($affiche["description"]); ?></td>
                    <td>
                        <a href="modifcreate.php?param=<?php echo $affiche["id_proposition"]; ?>">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </td>
                    <td>
                        <a href="suppcreate.php?param=<?php echo $affiche["id_proposition"]; ?>" onclick="return confirm('Voulez-vous supprimer ?');">
                            <i class="fa-solid fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="myModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
</div>

<script>
    // Modal image
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img01");

    document.querySelectorAll(".img-thumbnail").forEach(function(img) {
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
        };
    });

    var span = document.getElementsByClassName("close")[0];
    span.onclick = function() {
        modal.style.display = "none";
    };
</script>

</body>
</html>