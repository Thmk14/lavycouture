<?php 
    include ("config.php");
    $para=$_GET["param"];
    $requete="DELETE FROM proposition_modele WHERE id_proposition=?";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute([$para]);
    header("location:listcreate.php");
?>