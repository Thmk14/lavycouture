<?php 
    include ("config.php");
    
    $para = $_GET["param"];
    
    $requete = "DELETE FROM mensuration WHERE id_mensuration = ?";
    $prepare = $pdo->prepare($requete);
    $execute = $prepare->execute([$para]);
    
    // Rediriger vers la page précédente si elle existe, sinon vers listmesure.php
    if (!empty($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } 
    exit();
?>
