<?php 
    include ("config.php");
    $para=$_GET["param"];
    $requete="DELETE FROM vetement WHERE id_vetement=?";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute([$para]);
    header("location:listvet.php");
?>