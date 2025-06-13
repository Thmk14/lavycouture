<?php
require 'session.php';
require 'config.php';

$id_client = $_SESSION['id'];

$sql = "SELECT lca.*, art.nom_modele, art.image, art.prix
        FROM lien_commande_article lca
       JOIN article ON lca.id_article = art.id_article 
        ";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>