<?php
require("config.php");
$message = "";

if (isset($_POST["envoie"])) {
    $nom = trim($_POST["nom"]);
    $prenom = trim($_POST["prenoms"]);
    $email = trim($_POST["email"]);
    $tel = trim($_POST["tel"]);
    $msg = trim($_POST["message"]);

    // Vérifie que le client existe
    $verif = $pdo->prepare("SELECT id_client FROM client WHERE nom = ? AND prenom = ? AND email = ?");
    $verif->execute([$nom, $prenom, $email]);
    $client = $verif->fetch();

    if ($client) {
        // Met à jour le message
        $requete = "UPDATE client SET telephone = ?, message = ? WHERE id_client = ?";
        $prepare = $pdo->prepare($requete);
        $execute = $prepare->execute([$tel, $msg, $client['id_client']]);

        $message = $execute
            ? "<script > alert('Votre message a bien été envoyé ! ✅'); window.location.href='index.php'</script>"
            : "<p style='color:red;'> Erreur lors de l'envoi du message. ❌</p>";
    } else {
        $message = "<script > alert('Vous devez être inscrit pour envoyer un message ! ❌'); '</script>" ;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/brands.min.css" integrity="sha512-9YHSK59/rjvhtDcY/b+4rdnl0V4LPDWdkKceBl8ZLF5TB6745ml1AfluEU6dFWqwDw9lPvnauxFgpKvJqp7jiQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="stylesheet" href="css/contact.css">
    <title>Document</title>
</head>
<body>
<?php include 'menu.php'; ?>


<section class="contact">
  
        <div class="content">
          
        </div>
        <div class="message-box">
              <?php if (!empty($message)) echo $message; ?>
          </div>
        <div class="contain">
           <div class="contactinfo">
             <div class="box">
               <div class="inc"><i class="fa-sharp fa-solid fa-location-dot"></i></div>
               <div class="text">
                <h3>Localisation</h3>
                <p>Abidjan ,Marcory Sicogi<br>non loin du grand marché <br> Ouvert du lundi au samedi <br> 8h30 - 20h30</p>
               </div>
               
             </div>
             <div class="box">
              <div class="inc"><i class="fa-solid fa-phone"></i></div>
              <div class="text">
                <h3>Téléphone</h3>
                <p>+225 07 07 52 16 52</p>
              </div>
              
             </div>
             <div class="box">
              <div class="inc"><i class="fa-solid fa-envelope"></i></div>
              <div class="text">
                <h3>Email</h3>
                <p>lavy_couture@gmail.com</p>
              </div>
              
             </div>
           </div>
           

           <div class="container">
           
           <form method="POST" action="">
             <h1>Contactez-nous</h1>
             <input type="text" name="nom" placeholder="Nom" required>
             <input type="text" name="prenoms" placeholder="Prénoms" required>
             <input type="email" name="email" placeholder="Email" required>
             <input type="text" name="tel" placeholder="Téléphone" required>
             <h4>Écrivez votre message ici</h4>
             <textarea name="message" placeholder="Votre message..." required></textarea>
             <input type="submit"   name="envoie" value="Envoyer" id="button">
           </form>

           </div>
        </div>

        
    </section>

    
    <?php include 'footer.php'; ?>
    
</body>
</html>