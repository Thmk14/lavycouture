<?php
require 'session.php';
include 'config.php';

if (!$isLoggedIn) {
    header("Location: connexion.php");
    exit();
}

if (!isset($_GET['id_article']) || !is_numeric($_GET['id_article'])) {
    exit("ID non valide.");
}

$id_article = (int)$_GET['id_article'];
$stmt = $pdo->prepare("SELECT * FROM article WHERE id_article = ?");
$stmt->execute([$id_article]);
$produit = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produit) {
    exit("Produit non trouvé.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
    $id_client = $_SESSION['id_client'];
    $quantite = max(1, (int)$_POST['product_quantity']);
    $taille = trim($_POST['taille'] ?? '');

    // Validation taille
    $taille_autorisees = ['XS', 'S', 'M', 'L', 'XL', 'Mesures', "Prendre à l'atelier"];
    if (!in_array($taille, $taille_autorisees)) {
        exit("Taille non valide.");
    }

    $tissu = null;
    $personnalisation = null;
    $supplement = 0;

    // Gestion personnalisation
    if (!empty($_POST['enable_personnalisation'])) {
        $personnalisation_details = [];

        if (!empty($_POST['desc_personnalisation'])) {
            $personnalisation_details[] = "Description: " . trim($_POST['desc_personnalisation']);
        }

        $personnalisation = implode(" | ", $personnalisation_details);
        $supplement = 3000;
    }

    // Upload tissu (optionnel)
    if (isset($_FILES['tissu']) && $_FILES['tissu']['error'] === 0) {
        $fichier = $_FILES['tissu'];
        $nomFichier = basename($fichier['name']);
        $tmpName = $fichier['tmp_name'];
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($nomFichier, PATHINFO_EXTENSION));

        // Contrôle extension + taille max 5Mo
        if (in_array($ext, $allowed) && $fichier['size'] <= 5 * 1024 * 1024) {
            $newTissuName = uniqid('tissu_', true) . '.' . $ext;
            $uploadPath = 'uploads/' . $newTissuName;
            if (move_uploaded_file($tmpName, $uploadPath)) {
                $tissu = $newTissuName;
            } else {
                exit("Erreur lors de l'upload du tissu.");
            }
        } else {
            exit("Format ou taille du fichier tissu invalide.");
        }
    }


    // Insertion mensuration seulement si taille "Mesures" choisie
    $id_mensuration = null;
    if ($taille === 'XS' || $taille === 'S' || $taille === 'M' || $taille === 'L' || $taille === 'XL') {
        $stmt = $pdo->prepare("INSERT INTO mensuration (taille_standard) VALUES (?)");
        $stmt->execute([$taille]);
        $id_mensuration = $pdo->lastInsertId();
    }

    // Ajouter dans lien_commande_article
    $stmt = $pdo->prepare("INSERT INTO lien_commande_article (id_client, id_article, quantite, description, tissu, supplement_prix, id_mensuration) VALUES ( ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_client, $id_article, $quantite, $personnalisation, $tissu, $supplement, $id_mensuration]);

    echo "<script>alert('Produit ajouté au panier avec succès ! ✅');</script>";
    header("Location: panier.php");
    exit();
}
?>



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détail du produit</title>
    <link rel="stylesheet" href="css/detail.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" />
</head>
<body>

<?php include 'menu.php'; ?>

<div class="flex-box">
    <div class="left">
        <div class="big-img">
            <img src="uploads/<?= htmlspecialchars($produit['image']); ?>" alt="<?= htmlspecialchars($produit['nom_modele']); ?>">
        </div>
    </div>

    <div class="right">
        <h1><?= htmlspecialchars($produit['nom_modele']); ?></h1>
        <div class="titl"><?= nl2br(htmlspecialchars($produit['description'])); ?></div>
        <div class="price"><?= number_format($produit['prix'], 0, ',', ' '); ?> FCFA</div>

        <form method="POST" enctype="multipart/form-data">
    
    
    <label>Taille :</label>
    <select name="taille" required>
        <option value="XS">XS</option>
        <option value="S">S</option>
        <option value="M">M</option>
        <option value="L">L</option>
        <option value="XL">XL</option>
        <option value="Mesures">Voir mes mesures</option>
        <option value="Prendre à l'atelier">Prendre à l'atelier</option>
    </select>

    <label>
        <input type="checkbox" id="enable_personnalisation" name="enable_personnalisation">
        Je souhaite personnaliser ce vêtement (+3000 FCFA)
    </label>


    
    <div id="personnalisation_fields" style="display: none;">
         

        <label>Description du modèle souhaité:</label>
        <textarea name="desc_personnalisation" rows="3" placeholder="Décrivez votre personnalisation"></textarea>

        <label for="tissu">Type de tissu (image, facultatif) :</label>
        <input type="file" name="tissu" id="image" accept="image/*">

        <a href="mesure_manuel.php" class="btn-secondary">Cliquez pour entrer vos mesures</a>
    </div>


    <label>Quantité :</label>
    <div class="quantity-container">
        <button type="button" id="moins">-</button>
        <input type="number" id="quantite" name="product_quantity" value="1" min="1">
        <button type="button" id="plus">+</button>
    </div>

    <div class="btn-box">
        <input type="submit" name="add_to_cart" class="cart-btn" value="Ajouter au panier">
    </div>
</form>

    </div>
</div>

<script>
    const moins = document.getElementById("moins");
    const plus = document.getElementById("plus");
    const quantite = document.getElementById("quantite");

    moins.addEventListener("click", () => {
        let val = parseInt(quantite.value);
        if (val > 1) quantite.value = val - 1;
    });

    plus.addEventListener("click", () => {
        let val = parseInt(quantite.value);
        quantite.value = val + 1;
    });

    // Gestion de la personnalisation
    const checkboxPerso = document.getElementById("enable_personnalisation");
    const champsPerso = document.getElementById("personnalisation_fields");

    checkboxPerso.addEventListener("change", () => {
        if (checkboxPerso.checked) {
            champsPerso.style.display = "block";
        } else {
            champsPerso.style.display = "none";
        }
    });

    // Affiche les champs si la case est cochée au rechargement (utile après un retour arrière navigateur)
    window.addEventListener("DOMContentLoaded", () => {
        if (checkboxPerso.checked) {
            champsPerso.style.display = "block";
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>
