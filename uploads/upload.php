<?php
if (isset($_POST['btn-upload']) && isset($_FILES['photo'])) {
    $image = $_FILES['photo'];
    $imageName = basename($image['name']);
    $imageTmp = $image['tmp_name'];
    $imageSize = $image['size'];
    $imageError = $image['error'];

    $uploadDir = "uploads/";
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    // Vérification
    if (!in_array($ext, $allowedExtensions)) {
        echo "Format non autorisé.";
        exit;
    }

    if ($imageError === 0 && $imageSize <= 5 * 1024 * 1024) { // 5 Mo max
        $newImageName = uniqid("img_") . "." . $ext;
        $destination = $uploadDir . $newImageName;

        if (move_uploaded_file($imageTmp, $destination)) {
            echo "Image envoyée avec succès !<br>";
            echo "<img src='$destination' alt='Image envoyée' width='200'>";
        } else {
            echo "Erreur lors du téléchargement.";
        }
    } else {
        echo "Image trop lourde ou erreur inconnue.";
    }
}
?>
