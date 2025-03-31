function ouvrirModal(id) {
    document.getElementById(id).style.display = "flex";
}

function fermerModal(id) {
    document.getElementById(id).style.display = "none";
}

 // Sélectionner tous les liens de navigation
const navLinks = document.querySelectorAll('.sidebar ul li a');

// Obtenir uniquement le chemin de l'Url actuelle (sans le domaine)
const currentPath = window.location.pathname; 

// Vérifier quel lien correspond au chemin indiqué actuelle
navLinks.forEach(link => {
    if (link.pathname === currentPath) {  
        link.classList.add('active'); // Ajoute la classe active au lien correspondant
    }
});
