<?php
require 'session.php';
include 'config.php';

$isLoggedIn = isset($_SESSION['id']);

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
    $id_client = $_SESSION['id'];
    $quantite = max(1, (int)$_POST['product_quantity']);
    $taille = trim($_POST['taille'] ?? '');

    // Validation taille
    $taille_autorisees = ['XS', 'S', 'M', 'L', 'XL', 'Mesures'];
    if (!in_array($taille, $taille_autorisees)) {
        exit("Taille non valide.");
    }
     $montant_total = null;
    $tissu = null;
    $personnalisation = null;
    $supplement = 0;
    $statut = 'En attente';

    // Gestion personnalisation
    if (!empty($_POST['enable_personnalisation'])) {
        $personnalisation_details = [];

        if (!empty($_POST['desc_personnalisation'])) {
            $personnalisation_details[] = trim($_POST['desc_personnalisation']);
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
    if (isset( $_SESSION['id_mensuration'])) {
    $id_mensuration = $_SESSION['id_mensuration'];
    // Tu peux l’utiliser ici pour remplir un champ ou le stocker
}
else{

    $id_mensuration = null;
    
}
    if ($taille === 'XS' || $taille === 'S' || $taille === 'M' || $taille === 'L' || $taille === 'XL' || $taille === 'Mesures') {
        $stmt = $pdo->prepare("INSERT INTO mensuration (taille_standard) VALUES (?)");
        $stmt->execute([ $taille]);
        $id_mensuration = $pdo->lastInsertId();
    }

    // Ajouter dans lien_commande_article
    $stmt = $pdo->prepare("INSERT INTO commande (id_client,id_mensuration, quantite,statut, description_modele, tissu, supplement_prix, montant_total,etat_commande) VALUES ( ?,?,?,?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id_client,$id_mensuration, $quantite, $statut, $personnalisation, $tissu, $supplement, $montant_total,0]);

    $id_commande = $pdo->lastInsertId();
    $stmt = $pdo->prepare("INSERT INTO concerner (id_commande, id_article) VALUES ( ?, ?)");
    $stmt->execute([$id_commande, $id_article]);


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
   document.addEventListener("DOMContentLoaded", () => {
    const checkboxPerso = document.getElementById("enable_personnalisation");
    const champsPerso = document.getElementById("personnalisation_fields");

    if (!checkboxPerso || !champsPerso) return;

    // Restaurer l'état depuis le localStorage
    const isChecked = localStorage.getItem("personnalisation_active") === "true";
    checkboxPerso.checked = isChecked;
    champsPerso.style.display = isChecked ? "block" : "none";

    // Mettre à jour le localStorage lors du changement
    checkboxPerso.addEventListener("change", () => {
        const actif = checkboxPerso.checked;
        champsPerso.style.display = actif ? "block" : "none";
        localStorage.setItem("personnalisation_active", actif);
    });
});

</script>

<?php include 'footer.php'; ?>
</body>
</html>
