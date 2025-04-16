function ouvrirModal(id) {
    document.getElementById(id).style.display = "flex";
}

function fermerModal(id) {
    document.getElementById(id).style.display = "none";
}

// Vérifier si "showModal=1" est dans l'URL*
//crée un objet permettant de lire les paramètres de l'URL.
const urlParams = new URLSearchParams(window.location.search);

//.get("showModal") récupère la valeur associée au paramètre showModal.
const showModal = urlParams.get("showModal");

if (showModal) {
    ouvrirModal("modal" + showModal); // Ouvre "modal2" si showModal=2
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

