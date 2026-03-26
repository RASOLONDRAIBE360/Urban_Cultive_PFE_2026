// Fonction pour afficher/masquer le formulaire de réponse
function openReplyForm(commentId) {
    const replyForm = document.getElementById(commentId);
    if (replyForm.style.display === 'none' || replyForm.style.display === '') {
        replyForm.style.display = 'block';
    } else {
        replyForm.style.display = 'none';
    }
}

// Sélectionner tous les liens de navigation
const navLinks = document.querySelectorAll('nav a');

// Obtenir l'URL actuelle
const currentUrl = window.location.href;

// Vérifier quel lien correspond à l'URL actuelle
navLinks.forEach(link => {
    // Si l'URL du lien correspond à l'URL actuelle, ajouter la classe 'active'
    if (link.href === currentUrl) {
        link.classList.add('active');
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
                    alert.style.transform = 'translateX(20px)';
                    setTimeout(() => alert.remove(), 600);
                }, 5000);
            }
        }
    });
});