<?php
include("config.php");
include("session.php");
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - Lavi Couture | Réponses à vos questions</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/faq.css">k
</head>
<body>

<?php include 'menu.php'; ?>
    <div class="container">
        <header class="faq-header">
            <h1>Vos Questions, Nos Réponses</h1>
           
        </header>

        <section class="faq-section">
            <div class="faq-categories">
                <button class="category-button active" data-category="all">Tout voir</button>
                <button class="category-button" data-category="commandes">Commandes</button>
                <button class="category-button" data-category="mensurations">Mensurations</button>
                <button class="category-button" data-category="produits">Produits & Tissus</button>
                <button class="category-button" data-category="paiement-livraison">Paiement & Livraison</button>
                <button class="category-button" data-category="entretien">Entretien</button>
            </div>
            <div class="faq-search-bar">
                <input type="text" id="faqSearch" placeholder="Rechercher une question ou un mot-clé...">
            </div>
            <div class="faq-grid">
                <div class="faq-item" data-category="commandes">
                    <div class="faq-question">
                        Comment commander un article ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Chez Lavi Couture, vous avez deux options passionnantes ! Vous pouvez choisir un modèle existant parmi nos collections et le personnaliser selon vos envies (choix du tissu, ajustement de la taille, style des manches, etc.). Ou, si vous avez une vision unique, soumettez votre propre modèle détaillé via notre interface intuitive. Nous concrétisons vos rêves de mode !</p>
                    </div>
                </div>

                <div class="faq-item" data-category="mensurations">
                    <div class="faq-question">
                        Puis-je envoyer mes mensurations en ligne ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Absolument ! Lors du processus de commande, nous vous offrons une grande flexibilité pour la prise de mensurations. Vous pouvez soit entrer vos mesures manuellement avec précision, soit, pour une expérience simplifiée, soumettre une photo via notre système sécurisé qui effectuera une prise de mensurations automatique.</p>
                    </div>
                </div>

                <div class="faq-item" data-category="produits">
                    <div class="faq-question">
                        Quels types de tissus sont disponibles ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Lavi Couture est fière de vous proposer une <strong>vaste gamme de tissus</strong>, mettant en avant les richesses africaines telles que le pagne, le wax authentique, le bazin éclatant, et bien d'autres. Sur demande, nous pouvons également travailler avec une sélection de tissus modernes pour répondre à toutes vos préférences stylistiques.</p>
                    </div>
                </div>

                <div class="faq-item" data-category="commandes">
                    <div class="faq-question">
                        Quels sont les délais de confection ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>La confection de votre pièce unique est un art qui prend du temps. Le délai moyen est généralement de <strong>5 à 10 jours ouvrables</strong>, mais cela peut varier légèrement en fonction de la complexité du modèle choisi et de la demande actuelle. Soyez assuré(e) de recevoir une estimation précise et transparente lors de la validation de votre commande.</p>
                    </div>
                </div>

                <div class="faq-item" data-category="paiement-livraison">
                    <div class="faq-question">
                        Comment suivre ma commande ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Le suivi de votre commande est un jeu d'enfant ! Il vous suffit de vous connecter à votre compte personnel sur notre site. Une fois connecté(e), rendez-vous dans la section "Mes commandes" où vous pourrez consulter en temps réel l'état de votre commande : validée, en cours de confection, prête à être expédiée, ou livrée.</p>
                    </div>
                </div>

                <div class="faq-item" data-category="paiement-livraison">
                    <div class="faq-question">
                        Quels sont les moyens de paiement acceptés ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Pour votre commodité, Lavi Couture accepte plusieurs moyens de paiement sécurisés :</p>
                        <ul>
                            <li><strong>Mobile Money</strong> (via CinetPay pour une flexibilité maximale)</li>
                            <li><strong>Carte bancaire</strong> (Visa, MasterCard, etc.)</li>
                            <li><strong>Paiement à la livraison</strong> (disponible dans certaines localités, vérifiez les conditions lors du processus de commande).</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item" data-category="produits">
                    <div class="faq-question">
                        Puis-je commander des accessoires assortis à mon vêtement ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Oui, absolument ! Lavi Couture propose la confection d'accessoires assortis (sacs, bijoux, etc.) pour compléter parfaitement votre tenue personnalisée. Renseignez-vous auprès de notre équipe lors de votre commande pour explorer toutes les possibilités.</p>
                    </div>
                </div>

                <div class="faq-item" data-category="entretien">
                    <div class="faq-question">
                        Comment entretenir mon vêtement Lavi Couture ?
                        <span class="icon">▼</span>
                    </div>
                    <div class="faq-answer">
                        <p>Pour préserver la beauté et la longévité de votre vêtement Lavi Couture, nous recommandons généralement un lavage à la main à l'eau froide et un séchage à l'ombre. Des instructions d'entretien plus spécifiques seront fournies avec chaque pièce en fonction du tissu utilisé.</p>
                    </div>
                </div>

            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const faqItems = document.querySelectorAll('.faq-item');
            const faqSearchInput = document.getElementById('faqSearch');
            const categoryButtons = document.querySelectorAll('.category-button');

            // --- Fonction utilitaire pour masquer/afficher les FAQ et fermer les actives ---
            function filterFaqs(category = 'all', searchTerm = '') {
                searchTerm = searchTerm.toLowerCase().trim();

                faqItems.forEach(item => {
                    const itemCategory = item.dataset.category;
                    const questionText = item.querySelector('.faq-question').textContent.toLowerCase();
                    const answerText = item.querySelector('.faq-answer').textContent.toLowerCase();

                    const matchesCategory = (category === 'all' || itemCategory === category);
                    const matchesSearch = (searchTerm === '' || questionText.includes(searchTerm) || answerText.includes(searchTerm));

                    if (matchesCategory && matchesSearch) {
                        item.classList.remove('hidden');
                    } else {
                        item.classList.add('hidden');
                        // Ferme l'élément s'il est caché
                        item.classList.remove('active');
                        item.querySelector('.faq-answer').style.maxHeight = '0';
                    }
                });
            }

            // --- Logique d'accordéon pour l'ouverture/fermeture des réponses ---
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');

                question.addEventListener('click', () => {
                    // Ferme toutes les autres FAQ ouvertes
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item && otherItem.classList.contains('active')) {
                            otherItem.classList.remove('active');
                            otherItem.querySelector('.faq-answer').style.maxHeight = '0';
                        }
                    });

                    // Ouvre ou ferme la FAQ cliquée
                    item.classList.toggle('active');
                    if (item.classList.contains('active')) {
                        answer.style.maxHeight = answer.scrollHeight + 'px'; // Ajuste la hauteur de l'answer
                    } else {
                        answer.style.maxHeight = '0';
                    }
                });
            });

            // --- Logique de la recherche intelligente ---
            faqSearchInput.addEventListener('keyup', () => {
                const activeCategoryButton = document.querySelector('.category-button.active');
                const currentCategory = activeCategoryButton ? activeCategoryButton.dataset.category : 'all';
                filterFaqs(currentCategory, faqSearchInput.value);
            });

            // --- Logique de filtrage par catégories ---
            categoryButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Désactive le bouton actif actuel
                    document.querySelector('.category-button.active')?.classList.remove('active');
                    // Active le bouton cliqué
                    button.classList.add('active');

                    const selectedCategory = button.dataset.category;
                    // Réinitialise la barre de recherche lors du changement de catégorie
                    faqSearchInput.value = '';
                    filterFaqs(selectedCategory);
                });
            });

            // Initialiser l'affichage avec la catégorie 'all' au chargement de la page
            filterFaqs('all', '');
        });
    </script>
   
<?php include 'footer.php'; ?>
</body>
</html>