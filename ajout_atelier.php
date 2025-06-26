<?php
require 'session.php';
include 'config.php';

if (!isset($_GET['id_client']) || !is_numeric($_GET['id_client'])) {
    exit("ID non valide.");
}

$id_client = (int)$_GET['id_client'];

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart_custom'])) {
    $quantite = max(1, (int)$_POST['product_quantity']);
    $etat_commande = 1;
    $statut_commande = 'En attente';
    $visibilite = 1;
    $custom_image = null;
    $custom_tissu = null;
    $description_modele_custom = trim($_POST['desc_personnalisation'] ?? '');
    $montant_total_item = floatval(str_replace(',', '.', $_POST['custom_price'] ?? 0));

    $id_mensuration = $_SESSION['id_mensuration'] ?? null;

    if (!$id_mensuration) {
        $_SESSION['message_error'] = "Veuillez enregistrer vos mensurations avant de commander.";
        header("Location: mesure_manuel.php");
        exit();
    }

    function handle_upload($file, $prefix) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_exts) && $file['size'] <= 5 * 1024 * 1024) {
            $new_name = uniqid($prefix, true) . '.' . $ext;
            $dest = 'uploads/' . $new_name;
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                return $new_name;
            }
        }
        return false;
    }

    if (!empty($_FILES['image']['name'])) {
        $custom_image = handle_upload($_FILES['image'], 'model_');
        if (!$custom_image) {
            $_SESSION['message_error'] = "Erreur lors de l'upload de l'image du modèle.";
            header("Location: ajout_atelier.php?id_client=$id_client");
            exit();
        }
    }

    if (!empty($_FILES['tissu']['name'])) {
        $custom_tissu = handle_upload($_FILES['tissu'], 'tissu_');
        if (!$custom_tissu) {
            $_SESSION['message_error'] = "Erreur lors de l'upload du tissu.";
            header("Location: ajout_atelier.php?id_client=$id_client");
            exit();
        }
    }

    try {
        $pdo->beginTransaction();

        $stmt_article = $pdo->prepare("INSERT INTO article (image, visibilite) VALUES (?, ?)");
        $stmt_article->execute([$custom_image, $visibilite]);
        $id_article_new = $pdo->lastInsertId();

        $stmt_commande = $pdo->prepare("INSERT INTO commande (id_client, id_mensuration, tissu, description_modele, quantite, statut, montant_total, etat_commande) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_commande->execute([$id_client, $id_mensuration, $custom_tissu, $description_modele_custom, $quantite, $statut_commande, $montant_total_item, $etat_commande]);
        $id_commande_new = $pdo->lastInsertId();

        $stmt_concerner = $pdo->prepare("INSERT INTO concerner (id_commande, id_article) VALUES (?, ?)");
        $stmt_concerner->execute([$id_commande_new, $id_article_new]);

        $pdo->commit();

        $_SESSION['message_success'] = "Votre modèle personnalisé a été ajouté avec succès.";
        header("Location: commande_atelier.php?id_client=$id_client");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['message_error'] = "Erreur lors de l'ajout : " . $e->getMessage();
        header("Location: ajout_atelier.php?id_client=$id_client");
        exit();
    }
}

$message_success = $_SESSION['message_success'] ?? null;
$message_error = $_SESSION['message_error'] ?? null;
unset($_SESSION['message_success'], $_SESSION['message_error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Modèle Personnalisé</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/detail.css"> <style>
        /* Add or override styles from detail.css for this specific form */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            line-height: 1.6;
            padding-top: 20px;
        }

        .container {
            max-width: 800px;
            margin: 100px auto 50px auto; /* Adjust margin-top to clear header */
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #a72872;
            margin-bottom: 30px;
            font-size: 2.5em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: calc(100% - 22px); /* Account for padding and border */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }

        input[type="file"] {
            padding: 5px;
            background-color: #f9f9f9;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-group input {
            width: auto;
            margin-right: 10px;
        }

        #personnalisation_fields {
            background-color: #fefefe;
            border: 1px dashed #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            margin-top: 15px;
            transition: all 0.3s ease-in-out;
        }

        .quantity-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .quantity-container button {
            background-color: #f162ba;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        .quantity-container button:hover {
            background-color: #d14a9c;
        }

        .quantity-container input {
            width: 60px;
            text-align: center;
            -moz-appearance: textfield; /* Hide arrows for Firefox */
        }
        .quantity-container input::-webkit-outer-spin-button,
        .quantity-container input::-webkit-inner-spin-button {
            -webkit-appearance: none; /* Hide arrows for Chrome, Safari, Edge */
            margin: 0;
        }

        .btn-box {
            text-align: center;
            margin-top: 30px;
        }

        .cart-btn {
            background-color: #a72872;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .cart-btn:hover {
            background-color: #8a205a;
            transform: translateY(-2px);
        }

        .btn-secondary {
            display: inline-block;
            background-color: #17a2b8; /* Info blue */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9em;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #117a8b;
        }

        .message-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .message-box.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

    </style>
</head>
<body>

<a href="commande_atelier.php?id_client=<?= htmlspecialchars($id_client) ?>" class="add-button"><i class="fa-solid fa-plus"></i> Vos commandes</a>
        
<div class="container">
    <h1>Créez votre modèle personnalisé</h1>

    <?php if ($message_success): ?>
        <div class="message-box success"><?= htmlspecialchars($message_success) ?></div>
    <?php endif; ?>
    <?php if ($message_error): ?>
        <div class="message-box error"><?= htmlspecialchars($message_error) ?></div>
    <?php endif; ?>

    <form action="ajout_atelier.php?id_client=<?= $id_client ?>" method="POST" enctype="multipart/form-data">
        
       

        <div id="personnalisation_fields">
            <div class="form-group">
                <label for="image">Photo du modèle personnalisé :</label>
                <input type="file" name="image" id="image" accept="image/*" required>
                <small>Téléchargez une image de votre modèle (Max 5MB).</small>
            </div>

            <div class="form-group">
                <label for="custom_price">Prix souhaité pour ce modèle (FCFA) :</label>
                <input type="text" name="custom_price" id="custom_price" placeholder="Ex: 50000" pattern="[0-9]+([,\.][0-9]{1,2})?" title="Entrez un nombre (avec ou sans décimales)" required>
            </div>

            <div class="form-group">
                <label for="desc_personnalisation">Description détaillée du modèle souhaité :</label>
                <textarea name="desc_personnalisation" id="desc_personnalisation" rows="5" placeholder="Décrivez votre modèle (coupe, détails, etc.)" required></textarea>
            </div>

            <div class="form-group">
                <label for="tissu">Photo du type de tissu (facultatif) :</label>
                <input type="file" name="tissu" id="tissu" accept="image/*">
                <small>Téléchargez une image du tissu si vous en avez un (Max 5MB).</small>
            </div>

            <div class="form-group text-center">
                <a href="mesure_manuel.php" class="btn-secondary">Gérer mes mesures (optionnel)</a>
                <small style="display: block; margin-top: 10px;">Si vous souhaitez utiliser des mesures spécifiques pour cette commande, assurez-vous de les avoir enregistrées.</small>
            </div>
        </div>

        <div class="form-group">
            <label>Quantité :</label>
            <div class="quantity-container">
                <button type="button" id="moins">-</button>
                <input type="number" id="quantite" name="product_quantity" value="1" min="1" required>
                <button type="button" id="plus">+</button>
            </div>
        </div>

        <div class="btn-box">
            <input type="submit" name="add_to_cart_custom" class="cart-btn" value="Ajouter au panier">
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Quantity controls
        const moinsBtn = document.getElementById("moins");
        const plusBtn = document.getElementById("plus");
        const quantityInput = document.getElementById("quantite");

        if (moinsBtn && plusBtn && quantityInput) {
            moinsBtn.addEventListener("click", () => {
                let val = parseInt(quantityInput.value);
                if (val > 1) quantityInput.value = val - 1;
            });

            plusBtn.addEventListener("click", () => {
                let val = parseInt(quantityInput.value);
                quantityInput.value = val + 1;
            });
        }

        // Personalization fields toggle
        const checkboxPerso = document.getElementById("enable_personnalisation");
        const champsPerso = document.getElementById("personnalisation_fields");

        if (checkboxPerso && champsPerso) {
            // Initial state based on checkbox
            champsPerso.style.display = checkboxPerso.checked ? "block" : "none";

            // Add change listener
            checkboxPerso.addEventListener("change", () => {
                champsPerso.style.display = checkboxPerso.checked ? "block" : "none";
            });
        }

        // Form submission alert for required fields (basic client-side check)
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(event) {
                const customImageInput = document.getElementById('image');
                const customPriceInput = document.getElementById('custom_price');
                const descPersoTextarea = document.getElementById('desc_personnalisation');
                const quantityInput = document.getElementById('quantite');

                if (checkboxPerso.checked) {
                    if (!customImageInput.value) {
                        alert('Veuillez télécharger une photo pour votre modèle personnalisé.');
                        event.preventDefault();
                        return;
                    }
                    if (customPriceInput.value === '' || parseFloat(customPriceInput.value.replace(',', '.')) <= 0) {
                        alert('Veuillez entrer un prix valide pour votre modèle personnalisé.');
                        event.preventDefault();
                        return;
                    }
                    if (descPersoTextarea.value.trim() === '') {
                        alert('Veuillez fournir une description pour votre modèle personnalisé.');
                        event.preventDefault();
                        return;
                    }
                }

                if (parseInt(quantityInput.value) < 1) {
                    alert('La quantité doit être au moins 1.');
                    event.preventDefault();
                    return;
                }
            });
        }
    });
</script>

</body>
</html>