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

// Gestion de la disparition automatique des messages de session après 5 secondes
window.addEventListener('load', function () {
    let container = document.querySelector('.chic-alert-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'chic-alert-container';
        document.body.appendChild(container);
    }

    // Cibler uniquement les paragraphes qui sont susceptibles d'être des messages flash
    // On exclut les éléments à l'intérieur des titres (h1, h2, h3) pour éviter de capturer le nom d'utilisateur
    const candidateMessages = document.querySelectorAll('p');

    candidateMessages.forEach(msg => {
        // Vérifier si le message est dans un titre ou une section héro
        if (msg.closest('h1') || msg.closest('h2') || msg.closest('h3') || msg.closest('.hero')) {
            return;
        }

        const style = window.getComputedStyle(msg);
        const color = style.color;
        const text = msg.innerText.trim();

        if (text.length > 0 && text.length < 200) {
            let type = null;

            // Détection du vert (Success) - Nuances variées
            if (color.includes('rgb(0, 128, 0)') || color.includes('rgb(34, 197, 94)') || color.includes('rgb(22, 163, 74)') || color.includes('rgb(21, 128, 61)') || color.includes('rgb(16, 185, 129)') || color.includes('rgb(111, 136, 120)')) {
                type = 'success';
            }
            // Détection du rouge (Error)
            else if (color.includes('rgb(255, 0, 0)') || color.includes('rgb(239, 68, 68)') || color.includes('rgb(220, 38, 38)') || color.includes('rgb(153, 27, 27)') || color.includes('rgb(201, 42, 42)')) {
                type = 'error';
            }

            // Pour être sûr que c'est une alerte flash, on vérifie s'il y a un style en ligne important ou une position relative
            if (type && (msg.style.color || msg.style.position === 'relative')) {
                const alert = document.createElement('div');
                alert.className = `chic-alert ${type}`;
                alert.innerHTML = `<span>${text}</span>`;

                // Cacher l'original
                msg.style.display = 'none';
                msg.style.visibility = 'hidden';

                container.appendChild(alert);

                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateX(20px)';
                    setTimeout(() => alert.remove(), 600);
                }, 5000);
            }
        }
    });
});
