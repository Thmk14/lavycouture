<?php
require 'config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_client'])) exit;

$id_panier = $_POST['id_panier'] ?? null;
$quantite = intval($_POST['quantite'] ?? 0);

if ($id_panier && $quantite > 0) {
    $pdo->prepare("UPDATE panier SET quantite = ? WHERE id_panier = ? AND id_client = ?")
        ->execute([$quantite, $id_panier, $_SESSION['id_client']]);

    // Renvoyer le sous-total
    $stmt = $pdo->prepare("SELECT panier.quantite, vetement.prix 
                           FROM panier 
                           JOIN vetement ON panier.id_vetement = vetement.id_vetement 
                           WHERE panier.id_panier = ? AND panier.id_client = ?");
    $stmt->execute([$id_panier, $_SESSION['id_client']]);
    $item = $stmt->fetch();

    $sous_total = $item['quantite'] * $item['prix'];

    echo json_encode([
        "quantite" => $item['quantite'],
        "prix" => $item['prix'],
        "sous_total" => $sous_total
    ]);
} else {
    echo json_encode(["error" => "Erreur de paramètres"]);
}
