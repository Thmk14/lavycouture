<?php
include("config.php");

if (isset($_POST['proposer']) && isset($_FILES['image'])) {
    $image = $_FILES['image'];
    $imageName = basename($image['name']);
    $tmpName = $image['tmp_name'];
    $error = $image['error'];

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $error === 0) {
        $newName = uniqid("user_", true) . "." . $ext;
        $uploadPath = "uploads/" . $newName;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            // Insertion dans la base de données
            $stmt = $pdo->prepare("INSERT INTO proposition_modele 
                (type_vetement, type_tissu, longueur_manche, coupe, type_col,  existant, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $_POST['type_vetement'],
                $_POST['type_tissu'],
                $_POST['longueur_manche'],
                $_POST['coupe'],
                $_POST['type_col'],
               
                $newName,
                $_POST['description']
            ]);

            echo "<script>alert('Votre création a bien été enregistrée ! ✅ Merci pour votre contribution !'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Erreur lors de l\'enregistrement du fichier.');</script>";
        }
    } else {
        echo "<script>alert('Format d\'image non autorisé ou erreur.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre modèle</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/create.css">
</head>
<body>

<?php include 'menu.php'; ?>

<section>
    <div class="div">
        <h1>Créer votre modèle</h1>

        <form method="POST" enctype="multipart/form-data">

            <!-- Type de vêtement -->
            <label for="type">Type de vêtement :</label>
            <select id="type" name="type_vetement" required>
                <option value="robe">Robe</option>
                <option value="pantalon">Pantalon</option>
                <option value="chemise">Chemise</option>
                <option value="jupe">Jupe</option>
                <option value="veste">Veste</option>
                <option value="autre">Autre</option>
            </select>

            <!-- Tissu -->
            <label for="fabric">Type de tissu :</label>
            <select id="fabric" name="type_tissu" required>
                <option value="pagne">Pagne</option>
                <option value="bazin">Bazin</option>
                <option value="coton">Coton</option>
                <option value="satin">Satin</option>
                <option value="soie">Soie</option>
                <option value="autre">Autres</option>
            </select>

            <!-- Manches -->
            <label for="sleeve">Longueur des manches :</label>
            <select id="sleeve" name="longueur_manche" required>
                <option value="sans-manches">Sans manches</option>
                <option value="courtes">Manches courtes</option>
                <option value="trois-quart">Manches 3/4</option>
                <option value="longues">Manches longues</option>
                <option value="non">Non</option>
            </select>

            <!-- Coupe -->
            <label for="cut">Coupe :</label>
            <select id="cut" name="coupe" required>
                <option value="ajustee">Ajustée</option>
                <option value="evasee">Évasée</option>
                <option value="droite">Droite</option>
                <option value="oversize">Oversize</option>
                <option value="non">Non</option>
            </select>

            <!-- Col -->
            <label for="neck">Type de col :</label>
            <select id="neck" name="type_col" required>
                <option value="rond">Col rond</option>
                <option value="v">Col en V</option>
                <option value="chemise">Col chemise</option>
                <option value="col-montant">Col montant</option>
                <option value="off-shoulder">Off-shoulder</option>
                <option value="non">Non</option>
            </select>

           
            <!-- Existant -->
            <label for="image">Existant de votre modèle en image :</label> <br><br>
            <input type="file" name="image" id="image" accept="image/*" required>

            <!-- Description -->
            <textarea id="description" name="description" rows="4" placeholder="Décrivez les détails de votre modèle (inspirations, finitions, etc.)" required></textarea>

            <div class="buttons">
                <button class="submit" type="submit" name="proposer">Proposer ce modèle</button>
            </div>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>
