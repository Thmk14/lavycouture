<?php
require 'config.php';
require 'session.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Vérifie que le client est connecté
if (!isset($_SESSION['id'])) {
    echo "<p style='color:red;'>❌ Vous devez être connecté pour passer une commande.</p>";
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validation et assainissement des données
    $tel = trim($_POST['tel']);
    $mode = trim($_POST['mode']);
    $montant_total = trim($_POST['montant']) ?? 0;
    $pays = trim($_POST['pays']);
    $ville = trim($_POST['ville']);
    $lieu = trim($_POST['lieu']);
    $date = trim($_POST['date'] );
    
     $statut = 'Validée';
    // Vérification des champs obligatoires
    if (empty($tel) || empty($mode) || empty($pays) || empty($ville) || empty($lieu) ) {
        echo "<p style='color:red;'>❌ Tous les champs sont obligatoires.</p>";
        exit;
    }

    try {
        $pdo->beginTransaction();

        $idClient = $_SESSION['id'];
         
        // Mise à jour de l'adresse et téléphone
        $sqlUpdate = "UPDATE client SET pays = ?, ville = ?, lieu_habitation = ?, telephone = ? WHERE id_client = ?";
        $stmt = $pdo->prepare($sqlUpdate);
        $stmt->execute([$pays, $ville, $lieu, $tel, $idClient]);

        
        // Insertion de la commande
        $sqlCommande = "UPDATE commande SET etat_commande= ?,statut=?, date_commande=? ,mode_paiement=?,  montant_total = ? WHERE  id_client = ?  ";
        $stmt = $pdo->prepare($sqlCommande);
        $stmt->execute([1,$statut,$date, $mode, $montant_total, $idClient]);
        

        $pdo->commit();

        echo "<script>alert('✅ Commande enregistrée avec succès !'); window.location.href='detail_commande.php';</script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<p style='color:red;'>❌ Une erreur est survenue : " . $e->getMessage() . "</p>";
    }
}
?>




<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valider commandes</title>
    
    <style>
       /* --- Variables for better theming --- */
:root {
    --primary-color: #a72872; /* Darker pink/purple */
    --secondary-color: #f162ba; /* Lighter, vibrant pink */
    --background-color: #fed8f1; /* Light pink background */
    --card-background: #ffffff; /* White for containers */
    --text-color: #333333; /* Dark gray for general text */
    --light-text-color: #555555; /* Muted gray for labels */
    --border-color: #ddd; /* Light gray for input borders */
    --input-bg: #fce3f3; /* Light pink for input background */
    --shadow-light: rgba(0, 0, 0, 0.05);
    --shadow-medium: rgba(0, 0, 0, 0.1);
    --shadow-heavy: rgba(0, 0, 0, 0.2);
    --success-message-color: #28a745;
    --error-message-color: #dc3545;
}

/* --- Global Styles --- */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--background-color);
    margin: 0;
    padding: 0;
    line-height: 1.6;
    color: var(--text-color);
}

/* --- Main Container --- */
.form-container {
    width: 90%;
    max-width: 800px;
    margin: 60px auto;
    padding: 40px;
    background-color: var(--card-background);
    border-radius: 12px;
    box-shadow: 0 8px 20px var(--shadow-light);
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 40px;
    font-size: 2.5rem;
    font-weight: 700;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
}

/* --- Form Elements --- */
form label {
    display: block;
    margin-bottom: 10px;
    font-size: 1rem;
    color: var(--light-text-color);
    font-weight: 600;
}

form select,
form input[type="text"],
form input[type="number"],
form input[type="date"] {
    width: calc(100% - 22px); /* Account for padding and border */
    padding: 12px;
    margin-bottom: 30px; /* Increased space between inputs */
    border-radius: 8px;
    border: 1px solid var(--border-color);
    font-size: 1rem;
    background-color: var(--input-bg);
    transition: all 0.3s ease;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}

form select:focus,
form input[type="text"]:focus,
form input[type="number"]:focus,
form input[type="date"]:focus {
    border-color: var(--secondary-color);
    box-shadow: 0 0 0 3px rgba(241, 98, 186, 0.2);
    outline: none;
    background-color: #fff;
}

/* --- Buttons --- */
button {
    width: 100%;
    padding: 15px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.1rem;
    font-weight: 600;
    margin-top: 30px;
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 10px var(--shadow-light);
}

button:hover {
    background-color: #882162; /* Darker primary color on hover */
    transform: translateY(-2px);
    box-shadow: 0 6px 15px var(--shadow-medium);
}

/* Specific button styles */
#btn-payer {
    background-color: var(--secondary-color);
}

#btn-payer:hover {
    background-color: #d14a9c;
}

/* --- Table Styles --- */
table {
    width: 100%;
    border-collapse: separate; /* Use separate to allow border-spacing */
    border-spacing: 0 10px; /* Space between rows */
    margin-top: 30px;
    background-color: var(--card-background);
    border-radius: 12px;
    overflow: hidden; /* Ensures rounded corners are visible */
    box-shadow: 0 4px 15px var(--shadow-light);
}

table thead {
    background-color: var(--primary-color);
    color: white;
}

table th {
    padding: 15px;
    text-align: center;
    font-size: 0.95rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

table th:first-child { border-top-left-radius: 12px; }
table th:last-child { border-top-right-radius: 12px; }

table td {
    padding: 15px;
    text-align: center;
    vertical-align: middle;
    font-size: 0.95rem;
    color: var(--text-color);
    background-color: #fff; /* White background for cells */
    border-bottom: 1px solid var(--border-color); /* Separator between rows */
}

table tbody tr:last-child td {
    border-bottom: none; /* No border on the last row */
}

/* --- Image in Table --- */
table img {
    border-radius: 8px;
    width: 70px;
    height: 70px; /* Keep aspect ratio */
    object-fit: cover; /* Ensures image fills the space without distortion */
    border: 1px solid #eee;
    transition: transform 0.2s ease;
}

table img:hover {
    transform: scale(1.05);
}

/* --- Quantity Container (if present) --- */
.quantity-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}

.quantity-container input {
    width: 60px;
    text-align: center;
    border: 1px solid #ccc;
    background-color: #fff;
    border-radius: 6px;
    font-weight: bold;
    padding: 8px;
}

/* --- Total Section --- */
h3 {
    text-align: right;
    margin-top: 30px;
    font-size: 1.8rem;
    color: var(--primary-color);
    font-weight: 700;
    padding-right: 15px; /* Align with table content */
}

#total-general {
    font-size: 2rem;
    color: var(--secondary-color);
}

/* --- Payment Section (Kkiapay) --- */
#section-paiement {
    margin-top: 40px;
    padding: 25px;
    border: 2px dashed var(--secondary-color);
    border-radius: 10px;
    background-color: #fff0f9;
    text-align: center;
}

#section-paiement label {
    margin-bottom: 15px;
    font-size: 1.1rem;
    color: var(--primary-color);
}

#section-paiement input[type="number"] {
    margin-bottom: 20px;
    background-color: #fff;
    border-color: var(--secondary-color);
}

/* --- Modal Styles (for image zoom) --- */
.modal {
    display: none;
    position: fixed;
    z-index: 1000; /* Ensure it's on top */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.9);
    padding-top: 80px;
}

.modal-content {
    margin: auto;
    display: block;
    max-width: 90%;
    width: 700px;
    border-radius: 10px;
    box-shadow: 0 5px 30px var(--shadow-heavy);
}

.close {
    position: absolute;
    top: 25px;
    right: 40px;
    color: #f1f1f1;
    font-size: 45px;
    font-weight: bold;
    transition: 0.3s;
}

.close:hover,
.close:focus {
    color: #bbb;
    text-decoration: none;
    cursor: pointer;
}

/* --- PHP messages (add styling if needed) --- */
p[style*="color:red;"], p[style*="color:green;"] {
    text-align: center;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 8px;
    font-weight: 600;
}

p[style*="color:red;"] {
    background-color: #f8d7da;
    color: var(--error-message-color) !important;
    border: 1px solid #f5c6cb;
}

p[style*="color:green;"] {
    background-color: #d4edda;
    color: var(--success-message-color) !important;
    border: 1px solid #c3e6cb;
}

/* --- Responsive Adjustments --- */
@media (max-width: 1024px) {
    .form-container {
        margin: 40px auto;
        padding: 30px;
    }

    h1 {
        font-size: 2.2rem;
        margin-bottom: 30px;
    }

    table th, table td {
        padding: 12px;
        font-size: 0.9rem;
    }

    table img {
        width: 60px;
        height: 60px;
    }

    h3 {
        font-size: 1.6rem;
    }

    #total-general {
        font-size: 1.8rem;
    }
}

@media (max-width: 768px) {
    .form-container {
        width: 95%;
        margin: 30px auto;
        padding: 20px;
    }

    h1 {
        font-size: 2rem;
        margin-bottom: 25px;
    }

    form select,
    form input[type="text"],
    form input[type="number"],
    form input[type="date"] {
        padding: 10px;
        margin-bottom: 20px;
        width: calc(100% - 20px);
    }

    button {
        padding: 12px;
        font-size: 1rem;
        margin-top: 25px;
    }

    table {
        display: block;
        overflow-x: auto; /* Enable horizontal scrolling for smaller screens */
        white-space: nowrap; /* Prevent text wrapping within cells */
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    }

    table thead, table tbody, table th, table td, table tr {
        display: block; /* Make table elements behave like block elements */
    }

    table tr {
        margin-bottom: 15px; /* Add space between "rows" now that they are block elements */
        border: 1px solid var(--border-color);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    table td {
        border: none; /* Remove individual cell borders */
        text-align: left;
        position: relative;
        padding-left: 50%; /* Space for the pseudo-element label */
    }

    table td::before {
        content: attr(data-label); /* Use data-label for mobile labels */
        position: absolute;
        left: 10px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        text-align: left;
        font-weight: 600;
        color: var(--light-text-color);
    }

    table th {
        display: none; /* Hide original table headers */
    }

    table img {
        width: 50px;
        height: 50px;
    }

    h3 {
        font-size: 1.4rem;
        text-align: center;
        padding-right: 0;
    }

    #total-general {
        font-size: 1.6rem;
    }

    .modal-content {
        width: 95%;
        max-width: 500px; /* Constrain for very small screens */
    }

    .close {
        font-size: 35px;
        top: 20px;
        right: 30px;
    }
}

@media (max-width: 480px) {
    .form-container {
        padding: 15px;
        margin: 20px auto;
    }

    h1 {
        font-size: 1.8rem;
        margin-bottom: 20px;
    }

    form select,
    form input[type="text"],
    form input[type="number"],
    form input[type="date"] {
        padding: 8px;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }

    button {
        padding: 10px;
        font-size: 0.95rem;
        margin-top: 20px;
    }

    table td {
        font-size: 0.85rem;
        padding: 8px;
        padding-left: 45%;
    }

    table td::before {
        left: 8px;
        width: 40%;
        padding-right: 8px;
    }

    table img {
        width: 40px;
        height: 40px;
    }

    h3 {
        font-size: 1.2rem;
    }

    #total-general {
        font-size: 1.4rem;
    }

    .close {
        font-size: 30px;
        top: 15px;
        right: 20px;
    }
}

/* Adding data-label attributes to PHP generated table cells for mobile view */
/* This needs to be added directly in your PHP loop within the <td> tags. */
/* Example: <td data-label="Modèle"><?= $item['nom_modele'] ?></td> */
    </style>
    <script src="https://cdn.kkiapay.me/k.js"></script>
</head>
<body>




<div class="form-container">
   

    <h1>Confirmer la commande</h1>
   <form action="commande.php" method="POST" class="formulaire" id="formulaire-commande">

<label for="date">Date du jour</label>
<input type="date" id="date" name="date" required readonly>
    

    <label for="tel">Téléphone</label>
    <input type="text" id="tel" name="tel" placeholder="Téléphone" required>

   
    <label for="mode">Mode de paiement</label>
    <select name="mode" id="mode" required>
        <option value="">-- Option de paiement --</option>
        <option value="Paiement à la livraison">Paiement à la livraison</option>
        <option value="Paiement par mobile money">Paiement par mobile money</option>
    </select>


    <label for="pays">Pays</label>
    <select name="pays" id="pays" required>
        <option value="">-- Pays --</option>
        <option value="Côte d'Ivoire">Côte d'Ivoire</option>
    </select>

    <label for="ville">Ville</label>
    <select name="ville" id="ville" required>
        <option value="">-- Ville --</option>
        <option value="Abidjan">Abidjan</option>
        <option value="Yamoussoukro">Yamoussoukro</option>
    </select>



    <label for="lieu">Votre lieu d'habitation</label>
    <input type="text" id="lieu" name="lieu" placeholder="Lieu d'habitation" required>

   
        <?php $id_client = $_SESSION['id'];
        $query = "SELECT *
                  FROM concerner c
                  JOIN article art ON c.id_article = art.id_article
                  JOIN commande cmd ON c.id_commande = cmd.id_commande
                 JOIN mensuration m ON cmd.id_mensuration= m.id_mensuration
                  WHERE cmd.id_client = ? AND cmd.statut = 'En attente' AND cmd.etat_commande = 0
  
                  ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$id_client]);
        $articles = $stmt->fetchAll();
          $total = 0;
 ?>
<table>
    <thead>
    <tr>
        <th>Image</th>
        <th>Modèle</th>
        <th>Type tissu</th>
        <th>Quantité</th>
        <th>Taille</th>
        <th>Personnalisation</th>
        <th>Prix supplémentaire</th>
        <th>Prix</th>
        <th>Total</th>
       
    </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    <?php foreach ($articles as $item): ?>
        <?php $sous_total = $item['quantite'] * $item['prix']; ?>
        <?php $total += $sous_total; ?>
        <tr data-id="<?= $item['id_concerner'] ?>">
        <td><a href="javascript:void(0)"><img src="uploads/<?= htmlspecialchars($item['image'] ?? '') ?>" alt="Image Client" class="img-thumbnail" id="myImg"></td>
             
            <td><?= $item['nom_modele'] ?></td>
            <td><a href="javascript:void(0)"><img src="uploads/<?= htmlspecialchars(!empty($item['tissu']) ? $item['tissu'] : "Aucun") ?>" alt="Image Client" class="img-thumbnail" id="myImg"></td>
           
           

            <td><?= $item['quantite'] ?></td>
            <td><?= htmlspecialchars(!empty($item['taille_standard']) ? $item['taille_standard'] : "Aucun") ?></td>
            <td><?= htmlspecialchars(!empty($item['description_modele']) ? $item['description_modele'] : "Aucune") ?></td>
             <td><?= htmlspecialchars(!empty($item['supplement_prix']) ? $item['supplement_prix'] : "Aucune") ?></td>
            <td><?= $item['prix'] ?> FCFA</td>
            <td class="sous-total"><?= $sous_total ?> FCFA</td>
         </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3>Total général : <span id="total-general"><?= $total ?> FCFA</span></h3>


    <div id="section-paiement" style="display:none;">
        <label for="montant">Montant à payer (FCFA)</label>
        <input type="number" name="montant" id="montant">
        <button type="button" id="btn-payer" class="btn-payer">Payer maintenant</button>
    </div>


<button type="submit" name="btn-soumettre" id="btn-soumettre">Valider la commande</button>

    </form>
</div>



<!-- La modale -->
<div id="myModal" class="modal">
  <span class="close">&times;</span>
  <img class="modal-content" id="img01">
</div>

<script>
  // Obtenez la modal
var modal = document.getElementById("myModal");

// Obtenez le conteneur d'image modale
var modalImg = document.getElementById("img01");

// Trouver toutes les images avec la classe 'img-thumbnail'
document.querySelectorAll(".img-thumbnail").forEach(function(img) {
    img.onclick = function() {
        modal.style.display = "block";
        modalImg.src = this.src; // Charger l'image dans la modale
    };
});

// Quand l'utilisateur clique sur le bouton de fermeture, fermez la modale
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
    modal.style.display = "none";
};

document.addEventListener('DOMContentLoaded', function() {
    // Get the date input field
    const dateInput = document.getElementById('date');

    // Create a new Date object for the current date
    const today = new Date();

    // Format the date to YYYY-MM-DD, which is the required format for input type="date"
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day = String(today.getDate()).padStart(2, '0');

    const formattedDate = `${year}-${month}-${day}`;

    // Set the value of the date input field
    dateInput.value = formattedDate;
});

</script>
<script src="js/main.js"></script>




</body>


</html>
