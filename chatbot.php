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
    
    <style>
        :root {
            --primary-color:rgb(152, 57, 119); /* Couleur principale pour les titres et icônes */
            --secondary-color:rgb(209, 165, 192); /* Couleur secondaire pour les surlignages */
            --background-light:rgb(255, 246, 251); /* Fond très clair */
            --background-medium:rgb(255, 236, 246); /* Fond des sections */
            --background-card:rgb(255, 255, 255); /* Fond des cartes FAQ */
            --text-dark:rgb(83, 21, 51); /* Texte principal foncé */
            --text-light:rgb(91, 64, 77); /* Texte des réponses */
            --border-color:rgb(232, 207, 221); /* Couleur des bordures */
            --hover-bg:rgb(252, 200, 229); /* Fond au survol */
            --transition-speed: 0.3s;
        }

        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .faq-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            color: var(--primary-color);
            margin: 150px 0 10px 0;
            font-weight: 700;

        }

        .faq-header p {
            font-size: 18px;
            color: var(--text-light);
            max-width: 700px;
            margin: 0 auto;
        }

        .faq-section {
            background-color: var(--background-medium);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
        }

        /* Styles pour la barre de recherche */
        .faq-search-bar {
            margin-bottom: 30px;
            text-align: center;
        }

        .faq-search-bar input[type="text"] {
            width: 100%;
            max-width: 500px;
            padding: 15px 20px;
            border: 2px solid var(--border-color);
            border-radius: 30px;
            font-size: 17px;
            outline: none;
            transition: border-color var(--transition-speed) ease, box-shadow var(--transition-speed) ease;
            color: var(--text-dark);
            background-color: var(--background-card);
        }

        .faq-search-bar input[type="text"]::placeholder {
            color: var(--text-light);
            opacity: 0.7;
        }

        .faq-search-bar input[type="text"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(155, 92, 92, 0.2);
        }

        /* Styles pour les catégories */
        .faq-categories {
            text-align: center;
            margin-bottom: 25px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 10px;
        }

        .faq-categories button {
            background-color: var(--background-card);
            border: 1px solid var(--border-color);
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            white-space: nowrap; /* Empêche le texte de se casser sur plusieurs lignes */
        }

        .faq-categories button:hover {
            background-color: var(--hover-bg);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .faq-categories button.active {
            background-color: var(--primary-color);
            color: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .faq-grid {
            display: grid;
            gap: 20px;
        }

        .faq-item {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            background-color: var(--background-card);
            transition: all var(--transition-speed) ease;
        }

        .faq-item.hidden {
            display: none; /* Cache les éléments qui ne correspondent pas à la recherche ou au filtre */
        }

        .faq-question {
            padding: 20px 25px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 600;
            background-color:rgb(253, 223, 238); /* Doux fond pour les questions */
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: background-color var(--transition-speed) ease;
            color: var(--text-dark);
        }

        .faq-question:hover {
            background-color: var(--hover-bg);
        }

        .faq-question .icon {
            font-size: 24px;
            color: var(--primary-color);
            transition: transform var(--transition-speed) ease;
        }

        .faq-item.active .faq-question .icon {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 25px;
            max-height: 0;
            overflow: hidden;
            transition: max-height var(--transition-speed) ease-out, padding var(--transition-speed) ease-out;
            color: var(--text-light);
            font-size: 16px;
        }

        .faq-item.active .faq-answer {
            max-height: 200px; /* Ajusté pour du texte simple, augmentez si les réponses sont très longues */
            padding-bottom: 20px;
        }

        /* Effet d'ouverture */
        .faq-item.active .faq-question {
            background-color: var(--hover-bg); /* Garder le fond survolé une fois actif */
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        @media (max-width: 768px) {
            .faq-header h1 {
                font-size: 38px;
            }
            .faq-header p {
                font-size: 16px;
            }
            .faq-section {
                padding: 30px 20px;
            }
            .faq-question {
                font-size: 17px;
                padding: 18px 20px;
            }
            .faq-question .icon {
                font-size: 20px;
            }
            .faq-answer {
                font-size: 15px;
                padding: 0 20px;
            }
            .faq-categories button {
                padding: 8px 15px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .faq-header h1 {
                font-size: 32px;
            }
            .faq-question {
                font-size: 16px;
            }
            .faq-section {
                margin: 30px auto;
                padding: 25px 15px;
            }
            .faq-search-bar input[type="text"] {
                font-size: 15px;
                padding: 12px 18px;
            }
        }
    </style>
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

</body>
</html>