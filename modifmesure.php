<?php 
include("config.php");

// Récupérer l'ID du produit à modifier
$para = $_GET["param"] ?? null;

if (!$para) {
    echo "Produit introuvable.";
    exit();
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom_modele"];
    $categorie = $_POST["categorie_personne"];
    $prix = $_POST["prix"];
    $description = $_POST["description"];

    // Gestion de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $img_name = $_FILES['image']['name'];
        $img_tmp = $_FILES['image']['tmp_name'];
        $img_ext = strtolower(pathinfo($img_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($img_ext, $allowed)) {
            $nouveau_nom = uniqid() . '.' . $img_ext;
            $chemin = 'img/' . $nouveau_nom;

            if (move_uploaded_file($img_tmp, $chemin)) {
                $image_finale = $nouveau_nom;
            } else {
                echo "Erreur lors de l’upload de l’image.";
                exit();
            }
        } else {
            echo "Format de fichier non autorisé.";
            exit();
        }
    } else {
        // Si aucune nouvelle image, on garde l’ancienne
        $image_finale = $_POST['ancienne_image'];
    }

    // Mise à jour dans la base
    $requete = "UPDATE vetement SET nom_modele=?, categorie_personne=?, prix=?, image=?, description=? WHERE id_vetement=?";
    $prepare = $pdo->prepare($requete);
    $execute = $prepare->execute([$nom, $categorie, $prix, $image_finale, $description, $para]);

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
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/bootstrap.css">
     <link rel="stylesheet" href="css/bootstrap.min.css">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" integrity="sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="css/connexion.css">
    
</head>
<body>


        
<?php include 'catmenu.php'; ?>


<div class="form-box active" > 
<form action="" method="POST" enctype="multipart/form-data">
    <h2 class="h2">Modification du produit</h2>

    <label>Nom du modèle</label>
    <input name="nom_modele" type="text" value="<?php echo ($affiche["nom_modele"]); ?>" required>
        <input type="text" id="nom" name="nom" placeholder="Nom " required>

        <input type="text" id="nom" name="prenom" placeholder=" Prénoms" required>

        <input type="text" id="tel"  name="tel" placeholder="Téléphone" required>

        <input type="text" id="hbt" name="hbt" placeholder="Lieu d'habitation" required>


        <label for="lieu_prise">Lieu de prise</label>
        <select name="lieu_prise" id="">
            <option value="Via l'application">Via l'application</option>
            <option value="A l'atelier">A l'atelier</option>
        </select>
        

        <label for="taille">Tour de taille (cm)</label>
        <input type="number" id="taille" name="taille" value="<?php echo ($affiche["tour_taille"]); ?>" required>

        <label for="poitrine">Tour de poitrine (cm)</label>
        <input type="number" id="poitrine" name="poitrine" required>

        <label for="hanche">Tour de hanche (cm)</label>
        <input type="number" id="hanche" name="hanche" required>

        <label for="taille_de_buste">Taille du buste (cm)</label>
        <input type="number" id="taille_de_buste" name="taille_de_buste" required>

        <label for="bras">Longueur du bras (cm)</label>
        <input type="number" id="bras" name="bras" required>

        <label for="tbras">Tour de bras (cm)</label>
        <input type="number" id="tbras" name="tbras" required>

        <label for="longueur_jambe">Longueur de jambe (cm)</label>
        <input type="number" id="longueur_jambe" name="longueur_jambe" required>

        <label for="cuisse">Tour de cuisse (cm)</label>
        <input type="number" id="cuisse" name="cuisse" required>

        <label for="cou">Tour de cou (cm)</label>
        <input type="number" id="cou" name="cou" required>

        <label for="epaule">Largeur des épaules (cm)</label>
        <input type="number" id="epaule" name="epaule" required>

        <label for="entrejambe">Longueur de l'entrejambe (cm)</label>
        <input type="number" id="entrejambe" name="entrejambe" required>

        <label for="total">Longueur totale (cm)</label>
        <input type="number" id="total" name="total" required>



    <button type="submit">Modifier</button>
</form>

</div>



</body>
</html>