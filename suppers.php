<?php 
    include ("config.php");
    $para=$_GET["param"];
    $requete="DELETE FROM personnel WHERE id_personnel=?";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute([$para]);
    header("location:listpers.php");
?>