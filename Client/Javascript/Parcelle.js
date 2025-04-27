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
const idParc = urlParams.get('showModal'); // Récupèrer la valeur de la variable showModal passé en URL et 
    //le stocké dans la variable idParc qui sera ensuite utiliser pour représenter l'id du Modal qu'il faut ouvrir

    if (idParc) { // Vérifie si une valeur est présente
        ouvrirModal(idParc); // Appelle la fonction avec l'ID exact du parc
    }

let elements = document.querySelectorAll("p.date-fin");

    elements.forEach(element => {
        let dateLimite = element.getAttribute("data-date");//La fonction Date() converti
        //le format de notre date qui est le suivant : y-m-d comme telle : y-m-dTh:m:s
        let dateActuelle = new Date();
        //La lettre T est une lettre qui se trouve dans le format 
        //de notre date y-m-dTh:m:s et une fois que nous avons séparer les deux éléments
        //ils seront stockés dans un tableau comme telle : 
        //["2025-04-23", "14:35:12.000Z"] le chiffre 0 nous permet d'accèder à la première 
        //élément de notre tableau pour récupérer ainsi notre date
        let dateFormated = dateActuelle.toISOString().split('T')[0];

        if (dateLimite <= dateFormated) {
            element.classList.add("warning");
        }
    });