<?php
include("config.php");

// Récupérer l'ID du produit
$para = $_GET["param"] ?? null;
if (!$para) {
    echo "Produit introuvable.";
    exit();
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["nom_modele"];
    $categorie = $_POST["categorie_personne"];
    $description = $_POST["description"];
    $prix = $_POST["prix"];
    $ancienneImage = $_POST["ancienne_image"];

    $newImageName = $ancienneImage; // par défaut on garde l'ancienne image

    if (!empty($_FILES["image"]["name"])) {
        $image = $_FILES["image"];
        $imageName = basename($image['name']);
        $tmpName = $image['tmp_name'];
        $error = $image['error'];
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $error === 0) {
            $newImageName = uniqid("vetement_", true) . "." . $ext;
            $uploadPath = "uploads/" . $newImageName;

            if (!move_uploaded_file($tmpName, $uploadPath)) {
                echo "Erreur lors du téléchargement de l'image.";
                exit();
            }
        } else {
            echo "Image invalide ou format non autorisé.";
            exit();
        }
    }

    // Mise à jour dans la base
    $requete = "UPDATE vetement SET nom_modele=?, categorie_personne=?, prix=?, image=?, description=? WHERE id_vetement=?";
    $prepare = $pdo->prepare($requete);
    $execute = $prepare->execute([$name, $categorie, $prix, $newImageName, $description, $para]);

    if ($execute) {
        header("Location: listvet.php");
        exit();
    } else {
        echo "Erreur lors de la mise à jour du produit.";
    }
}

// Récupération des infos existantes
$requete = "SELECT * FROM vetement WHERE id_vetement=?";
$prepare = $pdo->prepare($requete);
$prepare->execute([$para]);
$affiche = $prepare->fetch(PDO::FETCH_ASSOC);

if (!$affiche) {
    echo "Produit non trouvé.";
    exit();
}
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier un produit</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/connexion.css">
</head>
<body>

<?php include 'catmenu.php'; ?>

<div class="form-box active">
    <form action="" method="POST" enctype="multipart/form-data">
        <h2>Modification du produit</h2>

        <label>Nom du modèle</label>
        <input name="nom_modele" type="text" value="<?= htmlspecialchars($affiche["nom_modele"]) ?>" required>

        <label>Catégorie personne</label>
        <input type="text" name="categorie_personne" value="<?= htmlspecialchars($affiche["categorie_personne"]) ?>" required>

        <label>Prix</label>
        <input name="prix" type="number" value="<?= htmlspecialchars($affiche["prix"]) ?>" min="0" required>

        <label>Description</label>
        <input type="text" name="description" value="<?= htmlspecialchars($affiche["description"]) ?>">

        <label>Image actuelle</label><br>
        <img src="uploads/<?= htmlspecialchars($affiche["image"]) ?>" width="150"><br><br>

        <label>Changer l'image (optionnel)</label>
        <input type="file" name="image" accept="image/*">

        <input type="hidden" name="ancienne_image" value="<?= htmlspecialchars($affiche["image"]) ?>">

        <button type="submit">Modifier</button>
    </form>
</div>

</body>
</html>
