<?php
include("config.php");

if (isset($_POST['proposer']) && isset($_FILES['image'])) {
    $image = $_FILES['image'];
    $imageName = basename($image['name']);
    $tmpName = $image['tmp_name'];
    $error = $image['error'];

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $error === 0) {
        $newName = uniqid("user_", true) . "." . $ext;
        $uploadPath = "uploads/" . $newName;

        if (move_uploaded_file($tmpName, $uploadPath)) {
            // Insertion dans la base de données
            $stmt = $pdo->prepare("INSERT INTO proposition_modele 
                (type_vetement, type_tissu, longueur_manche, coupe, type_col, existant, description) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->execute([
                $_POST['type_vetement'],
                $_POST['type_tissu'],
                $_POST['longueur_manche'],
                $_POST['coupe'],
                $_POST['type_col'],
                $newName,
                $_POST['description']
            ]);

            echo "<script>alert('Votre création a bien été enregistrée ! ✅ Merci pour votre contribution !'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Erreur lors de l\'enregistrement du fichier.');</script>";
        }
    } else {
        echo "<script>alert('Format d\'image non autorisé ou erreur.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer votre modèle - Lavy Couture</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/create.css">
</head>
<body>

<?php include 'menu.php'; ?>

<section>
    <div class="div">
        <h1><i class="fas fa-palette"></i> Créer votre modèle</h1>

        <form method="POST" enctype="multipart/form-data" id="createModelForm">
            
            <div class="form-group">
                <label for="type_vetement">
                    Type de vêtement
                </label>
                <select id="type_vetement" name="type_vetement" required>
                    <option value="">Sélectionnez un type</option>
                    <option value="robe">Robe</option>
                    <option value="pantalon">Pantalon</option>
                    <option value="chemise">Chemise</option>
                    <option value="jupe">Jupe</option>
                    <option value="veste">Veste</option>
                    <option value="autre">Autre</option>
                </select>
            </div>

            <div class="form-group">
                <label for="type_tissu"> Type de tissu
                </label>
                <select id="type_tissu" name="type_tissu" required>
                    <option value="">Sélectionnez un tissu</option>
                    <option value="pagne">Pagne</option>
                    <option value="bazin">Bazin</option>
                    <option value="coton">Coton</option>
                    <option value="satin">Satin</option>
                    <option value="soie">Soie</option>
                    <option value="autre">Autres</option>
                </select>
            </div>

            <div class="form-group">
                <label for="longueur_manche">
                     Longueur des manches
                </label>
                <select id="longueur_manche" name="longueur_manche" required>
                    <option value="">Sélectionnez la longueur</option>
                    <option value="sans-manches">Sans manches</option>
                    <option value="courtes">Manches courtes</option>
                    <option value="trois-quart">Manches 3/4</option>
                    <option value="longues">Manches longues</option>
                    <option value="non">Non applicable</option>
                </select>
            </div>

            <div class="form-group">
                <label for="coupe">
                     Coupe
                </label>
                <select id="coupe" name="coupe" required>
                    <option value="">Sélectionnez la coupe</option>
                    <option value="ajustee">Ajustée</option>
                    <option value="evasee">Évasée</option>
                    <option value="droite">Droite</option>
                    <option value="oversize">Oversize</option>
                    <option value="non">Non applicable</option>
                </select>
            </div>

            <div class="form-group">
                <label for="type_col">
                    Type de col
                </label>
                <select id="type_col" name="type_col" required>
                    <option value="">Sélectionnez le type de col</option>
                    <option value="rond">Col rond</option>
                    <option value="v">Col en V</option>
                    <option value="chemise">Col chemise</option>
                    <option value="col-montant">Col montant</option>
                    <option value="off-shoulder">Off-shoulder</option>
                    <option value="non">Non applicable</option>
                </select>
            </div>

            <div class="form-group">
                <label for="image">
                     Image de votre modèle
                </label>
                <input type="file" name="image" id="image" accept="image/*" required>
                
            </div>

            <div class="form-group">
                <label for="description">
                    Description détaillée
                </label>
                <textarea id="description" name="description" rows="4" 
                    placeholder="Décrivez les détails de votre modèle : inspirations, finitions, couleurs préférées, style souhaité, etc." 
                    required></textarea>
            </div>

            <div class="buttons">
                <button class="submit" type="submit" name="proposer" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Proposer ce modèle
                </button>
            </div>
        </form>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createModelForm');
    const submitBtn = document.getElementById('submitBtn');
    const fileInput = document.getElementById('image');
    
    // File input preview and validation
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Check file size (5MB limit)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Taille maximum : 5MB');
                this.value = '';
                return;
            }
            
            // Check file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Format de fichier non supporté. Utilisez JPG, PNG, GIF ou WEBP.');
                this.value = '';
                return;
            }
        }
    });
    
    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        // Add loading state
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        
        // Remove loading state after a delay (in case of error)
        setTimeout(() => {
            submitBtn.classList.remove('loading');
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Proposer ce modèle';
        }, 10000);
    });
    
    // Real-time form validation
    const inputs = form.querySelectorAll('select, textarea, input[type="file"]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('error')) {
                validateField(this);
            }
        });
    });
    
    function validateField(field) {
        const formGroup = field.closest('.form-group');
        
        if (field.hasAttribute('required') && !field.value.trim()) {
            formGroup.classList.add('error');
            formGroup.classList.remove('success');
        } else {
            formGroup.classList.remove('error');
            formGroup.classList.add('success');
        }
    }
    
    // Smooth scrolling for form navigation
    const labels = form.querySelectorAll('label');
    labels.forEach(label => {
        label.addEventListener('click', function() {
            const targetId = this.getAttribute('for');
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        });
    });
});
</script>

</body>
</html>
