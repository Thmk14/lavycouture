<?php 
    include ("config.php");
    $para=$_GET["param"];
    $requete="DELETE FROM client WHERE id_client=?";
    $prepare=$pdo->prepare($requete);
    $execute=$prepare->execute([$para]);
    header("location:listclient.php");
?>