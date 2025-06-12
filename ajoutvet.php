<?php 
include("config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["name"]) && isset($_FILES["image"])) {
    $name = $_POST["name"];
    $categorie = $_POST["categorie"];
    $description = $_POST["description"];
    $prix = $_POST["prix"];

    // Traitement de l'image
    $image = $_FILES["image"];
    $imageName = basename($image['name']);
    $tmpName = $image['tmp_name'];
    $error = $image['error'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $error === 0) {
        $newImageName = uniqid("vetement_", true) . "." . $ext;
        $uploadPath = "uploads/" . $newImageName;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            // Insertion dans la base
            $requete = "INSERT INTO vetement (nom_modele, categorie_personne, description, prix, image) VALUES (?, ?, ?, ?, ?)";
            $prepare = $pdo->prepare($requete);
            $tab = [$name, $categorie, $description, $prix, $newImageName];
            $execute = $prepare->execute($tab);

            if ($execute) {
                header("Location: listvet.php");
                exit();
            } else {
                echo "Erreur lors de l'ajout du vêtement.";
            }
        } else {
            echo "Erreur lors du téléchargement de l'image.";
        }
    } else {
        echo "Image invalide ou format non autorisé.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter vêtement</title>
    <link rel="stylesheet" href="css/connexion.css">
</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="container">
    <div class="form-box active" id="register-form">
        <form method="POST" enctype="multipart/form-data">
            <h2 class="h2">Ajout de produit (vêtement)</h2>

            <input type="text" name="name" placeholder="Nom du modèle" required>
            <input type="text" name="categorie" placeholder="Catégorie personne" required>
            <input type="text" name="description" placeholder="Description" required>
            <input type="number" name="prix" placeholder="Prix" required>
            <input type="file" name="image" id="image" accept="image/*" required>

            <button type="submit" name="register">Ajouter</button>
        </form>
    </div>
</div>




</body>
</html>

 