
document.addEventListener("DOMContentLoaded", function () {
    // Sélection des éléments du menu
    const menuToggle = document.querySelector(".menu-hamburger");
    const menu = document.querySelector(".nav-links");

    // Toggle (affichage/masquage) du menu en version mobile
    menuToggle.addEventListener("click", function () {
        menu.classList.toggle("mobile-menu");
    });

    // Effet de scroll pour rendre la navbar sticky
    window.addEventListener("scroll", function () {
        var navbar = document.querySelector("header");
        navbar.classList.toggle("sticky", window.scrollY > 0);

    });

   

  
});