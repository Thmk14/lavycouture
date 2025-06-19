<?php
require("config.php");
require("session.php");

if (isset($_GET['param'])) {
    $id_client = intval($_GET['param']);
    $_SESSION['client_en_cours'] = $id_client;
} elseif (isset($_SESSION['client_en_cours'])) {
    $id_client = $_SESSION['client_en_cours'];
} else {
    die("Aucun client sélectionné.");
}

$stmt = $pdo->prepare("SELECT nom, prenom FROM client WHERE id_client = ?");
$stmt->execute([$id_client]);
$client = $stmt->fetch();
$nom_prenom = $client ? $client['prenom'] . ' ' . $client['nom'] : '';

if (!isset($_SESSION['clients_data'])) {
    $_SESSION['clients_data'] = [];
}
if (!isset($_SESSION['clients_data'][$id_client])) {
    $_SESSION['clients_data'][$id_client] = [
        'temp_articles' => [],
        'temp_mensurations' => [],
        'mensurations_selectionnees' => []
    ];
}

$client_data = &$_SESSION['clients_data'][$id_client];

if (isset($_POST['supprimer_article'])) {
    $index = $_POST['index'];
    if (isset($client_data['temp_articles'][$index])) {
        unset($client_data['temp_articles'][$index]);
        $client_data['temp_articles'] = array_values($client_data['temp_articles']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['supprimer_mensuration'])) {
    $index = $_POST['index'];
    if (isset($client_data['temp_mensurations'][$index])) {
        unset($client_data['temp_mensurations'][$index]);
        $client_data['temp_mensurations'] = array_values($client_data['temp_mensurations']);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['associer_mensurations']) && isset($_POST['selected_mensurations'])) {
    $_SESSION['clients_data'][$id_client]['mensurations_selectionnees'] = array_map('intval', $_POST['selected_mensurations']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['ajouter_article'])) {
    $article = [
        'nom_modele' => $_POST['nom_modele'],
        'description_modele' => $_POST['description_modele'],
        'image' => $_FILES['image']['name'],
        'tissu' => $_FILES['tissu']['name'],
        'quantite' => $_POST['quantite'],
        'mensuration_indices' => $_SESSION['clients_data'][$id_client]['mensurations_selectionnees'] ?? []
    ];
    $client_data['temp_articles'][] = $article;
    $_SESSION['clients_data'][$id_client]['mensurations_selectionnees'] = [];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['ajouter_mensuration'])) {
    $mensuration = [
        'taille' => $_POST['taille'],
        'poitrine' => $_POST['poitrine'],
        'hanche' => $_POST['hanche'],
        'taille_de_buste' => $_POST['taille_de_buste'],
        'bras' => $_POST['bras'],
        'tbras' => $_POST['tbras'],
        'longueur_jambe' => $_POST['longueur_jambe'],
        'cuisse' => $_POST['cuisse'],
        'cou' => $_POST['cou'],
        'epaule' => $_POST['epaule'],
        'entrejambe' => $_POST['entrejambe'],
        'total' => $_POST['total']
    ];
    $client_data['temp_mensurations'][] = $mensuration;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Commande</title>
    <style>
        body {
            font-family: Arial;
            background: #fce4f7;
            margin: 0;
            padding: 20px;
        }
        .container {
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }
        .section {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            
        }
        h3 {
            text-align: center;
            margin-top: 0;
            color: #a72872;
        }
        .btn-ajouter {
            background: #a72872;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .btn-supprimer {
            background: #d9534f;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .info {
            margin-top: 15px;
            border-top: 2px dashed #a72872;
            padding-top: 15px;
            margin-bottom: 30px;
        }
        .info img {
            max-width: 100px;
            border-radius: 8px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, textarea, select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .paired {
            background-color: #fff5fa;
            padding: 10px;
            border-left: 4px solid #a72872;
        }
    </style>
</head>
<body>
    <a href="client_atelier.php"><button  class="btn-ajouter">Retour</button></a>
<h1>Client : <?= htmlspecialchars($nom_prenom) ?> </h1>
<div class="container">

<!-- Section Mensuration -->
    <div class="section">
         <h3>Commande</h3>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="text" name="nom_modele" placeholder="Nom du modèle" required>
            <textarea name="description_modele" placeholder="Description..." required></textarea>
            <input type="file" name="image" accept="image/*" required>
            <input type="file" name="tissu" accept="image/*" required>
            <input type="number" name="quantite" min="1" placeholder="Quantité" required>
            <button type="submit" name="ajouter_article" class="btn-ajouter">+ Ajouter commande</button>
        </form>
        
        <h3>Mensurations</h3>
        <form action="" method="POST">
        <input type="number" id="taille" name="taille"  placeholder=tour_taille required>
        <input type="number" id="poitrine" name="poitrine" required>
        <input type="number" id="hanche" name="hanche"  required>
        <input type="number" id="taille_de_buste" name="taille_de_buste" required>
        <input type="number" id="bras" name="bras" required>
        <input type="number" id="tbras" name="tbras" required>
        <input type="number" id="longueur_jambe" name="longueur_jambe" required>
        <input type="number" id="cuisse" name="cuisse" required>
        <input type="number" id="cou" name="cou" required>
        <input type="number" id="epaule" name="epaule" required>
        <input type="number" id="entrejambe" name="entrejambe" required>
        <input type="number" id="total" name="total" required>



            <button type="submit" name="ajouter_mensuration" class="btn-ajouter">+ Ajouter mensuration</button>
        </form>

        <form action="" method="POST">
            <label><strong>Sélectionner les mensurations à associer à la prochaine commande :</strong></label>
            <?php foreach ($client_data['temp_mensurations'] as $index => $m): ?>
                <div>
                    <input type="checkbox" name="selected_mensurations[]" value="<?= $index ?>"
                        <?= in_array($index, $_SESSION['clients_data'][$id_client]['mensurations_selectionnees'] ?? []) ? 'checked' : '' ?> >
                    
                        Mensuration #<?= $index + 1 ?> 
                        :
                    Tour de poitrine:<?= htmlspecialchars($m['tour_poitrine']) ?>,
                    Tour de taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_hanche']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>

                </div>
            <?php endforeach; ?>
            <button type="submit" name="associer_mensurations" class="btn-ajouter">Associer à la prochaine commande</button>
        </form>
    </div>

    
    <!-- Section Commande -->
    <div class="section">
       

        <?php foreach ($client_data['temp_articles'] as $index => $article): ?>
            <div class="info <?= !empty($article['mensuration_indices']) ? 'paired' : '' ?>">
                <p><strong>Commande #<?= $index + 1 ?></strong></p>
                <p><strong>Nom :</strong> <?= htmlspecialchars($article['nom_modele']) ?></p>
                <p><strong>Description :</strong> <?= htmlspecialchars($article['description_modele']) ?></p>
                <p><strong>Quantité :</strong> <?= htmlspecialchars($article['quantite']) ?></p>
                <img src="uploads/<?= htmlspecialchars($article['image']) ?>" alt="Image article">
                <img src="uploads/<?= htmlspecialchars($article['tissu']) ?>" alt="Tissu">
                <?php if (!empty($article['mensuration_indices'])): ?>
                    <p><em><strong>Mensurations associées :</strong></em></p>
                    <ul>
                        <?php foreach ($article['mensuration_indices'] as $mIndex): 
                            if (isset($client_data['temp_mensurations'][$mIndex])) {
                                $m = $client_data['temp_mensurations'][$mIndex]; ?>
                                <li>:
                    Tour de poitrine:<?= htmlspecialchars($m['tour_poitrine']) ?>,
                    Tour de taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_hanche']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                    Taille <?= htmlspecialchars($m['tour_taille']) ?>,
                    Bassin <?= htmlspecialchars($m['tour_bassin']) ?>
                                </li>
                        <?php } endforeach; ?>
                    </ul>
                <?php endif; ?>
                <form action="" method="POST">
                    <input type="hidden" name="index" value="<?= $index ?>">
                    <button type="submit" name="supprimer_article" class="btn-supprimer">Supprimer</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    
</div>
</body>
</html>
