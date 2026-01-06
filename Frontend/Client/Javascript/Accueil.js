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


    