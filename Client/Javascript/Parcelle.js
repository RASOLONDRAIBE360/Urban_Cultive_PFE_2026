function ouvrirModal(id) {
    document.getElementById(id).style.display = "flex";
}

function fermerModal(id) {
    document.getElementById(id).style.display = "none";
}

// Sélectionner tous les liens de navigation
const navLinks = document.querySelectorAll('nav a');

// Obtenir l'URL actuelle
const currentPath = window.location.pathname;

// Vérifier quel lien correspond à l'URL actuelle
navLinks.forEach(link => {
    // Si l'URL du lien correspond à l'URL actuelle, ajouter la classe 'active'
    if (link.pathname === currentPath) {
        link.classList.add('active');
    }
});

const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('showModal') === '1') {
    const id = 'modal1'; // Remplacez par l'ID de votre modal
    ouvrirModal(id);
}

let elements = document.querySelectorAll("p.date-fin");

    elements.forEach(element => {
        let dateFin = new Date(element.getAttribute("data-date"));
        let dateActuelle = new Date();
        
        // On compare uniquement les jours
        dateFin.setHours(0, 0, 0, 0);
        dateActuelle.setHours(0, 0, 0, 0);

        if (dateFin.getTime() <= dateActuelle.getTime()) {
            element.classList.add("expire");
        }
    });