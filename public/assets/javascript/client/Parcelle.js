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

// Gestion de la disparition automatique des messages de session après 5 secondes
window.addEventListener('load', function () {
    let container = document.querySelector('.chic-alert-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'chic-alert-container';
        document.body.appendChild(container);
    }

    const candidateMessages = document.querySelectorAll('p');

    candidateMessages.forEach(msg => {
        if (msg.closest('h1') || msg.closest('h2') || msg.closest('h3') || msg.closest('.hero') || msg.classList.contains('date-fin')) {
            return;
        }

        const style = window.getComputedStyle(msg);
        const color = style.color;
        const text = msg.innerText.trim();

        if (text.length > 0 && text.length < 200) {
            let type = null;

            if (color.includes('rgb(0, 128, 0)') || color.includes('rgb(34, 197, 94)') || color.includes('rgb(16, 185, 129)') || color.includes('rgb(45, 106, 79)')) {
                type = 'success';
            }
            else if (color.includes('rgb(255, 0, 0)') || color.includes('rgb(239, 68, 68)') || color.includes('rgb(153, 27, 27)')) {
                type = 'error';
            }

            if (type && (msg.style.color || msg.style.position === 'relative')) {
                const alert = document.createElement('div');
                alert.className = `chic-alert ${type}`;
                alert.innerHTML = `<span>${text}</span>`;

                msg.style.display = 'none';
                msg.style.visibility = 'hidden';

                container.appendChild(alert);

                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(() => alert.remove(), 600);
                }, 5000);
            }
        }
    });
});