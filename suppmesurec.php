<?php 
    include ("config.php");
    $para=$_GET["param"];
    $requete="DELETE FROM mensuration WHERE id_mensuration=?";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute([$para]);
    header("location:listmesurec.php");
?>