<?php
require 'config.php';
session_start();

$id_client = $_SESSION['id'];

// 1. Créer la commande
$stmt = $pdo->prepare("INSERT INTO commande (id_client, date_commande) VALUES (?, NOW())");
$stmt->execute([$id_client]);
$id_commande = $pdo->lastInsertId();

// 2. Ajouter l'article
$nom = $_POST['nom_modele'];
$desc = $_POST['description_modele'];

$image = $_FILES['image']['name'];
$tissu = $_FILES['tissu']['name'];

move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
move_uploaded_file($_FILES['tissu']['tmp_name'], 'uploads/' . $tissu);

$stmt = $pdo->prepare("INSERT INTO article (nom_modele, description_modele, image, tissu) VALUES (?, ?, ?, ?)");
$stmt->execute([$nom, $desc, $image, $tissu]);

$id_article = $pdo->lastInsertId();

// 3. Lier via `concerner`
$stmt = $pdo->prepare("INSERT INTO concerner (id_commande, id_article) VALUES (?, ?)");
$stmt->execute([$id_commande, $id_article]);

header("Location: historique_commandes.php");
exit();
