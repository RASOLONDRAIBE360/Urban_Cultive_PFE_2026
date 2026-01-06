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