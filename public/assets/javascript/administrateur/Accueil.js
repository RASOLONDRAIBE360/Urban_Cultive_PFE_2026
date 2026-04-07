/*
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
*/

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
        if (msg.closest('h1') || msg.closest('h2') || msg.closest('h3') || msg.closest('header')) {
            return;
        }

        const style = window.getComputedStyle(msg);
        const color = style.color;
        const text = msg.innerText.trim();

        if (text.length > 0 && text.length < 200) {
            let type = null;

            if (color.includes('rgb(0, 128, 0)') || color.includes('rgb(34, 197, 94)') || color.includes('rgb(16, 185, 129)') || color.includes('rgb(72, 122, 51)')) {
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
                    alert.style.transform = 'translateX(20px)';
                    setTimeout(() => alert.remove(), 600);
                }, 5000);
            }
        }
    });
});
