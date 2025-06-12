<?php
require 'config.php'; // connexion base de données

if (isset($_GET['id']) && isset($_GET['statut'])) {
    $id = intval($_GET['id']);
    $statut = htmlspecialchars($_GET['statut']);

    // Vérifie si une livraison existe déjà pour cette commande
    $check = $pdo->prepare("SELECT id_livraison FROM livraison WHERE id_commande = :id");
    $check->execute(['id' => $id]);
    $livraison = $check->fetch(PDO::FETCH_ASSOC);

    if ($livraison) {
        // S'il existe une livraison, on fait un UPDATE
        $sql = "UPDATE livraison SET statut_livraison = :statut WHERE id_commande = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'statut' => $statut,
            'id' => $id
        ]);
    } else {
        // Sinon, on crée une nouvelle livraison
        $sql = "INSERT INTO livraison (id_commande, statut_livraison) VALUES (:id, :statut)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'statut' => $statut
        ]);
    }

    // Rediriger vers la page livreur
    header('Location: livreur.php');
    exit();
} else {
    echo "Données manquantes.";
}
?>
