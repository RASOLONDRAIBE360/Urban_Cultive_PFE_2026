const toggleBtn = document.getElementById('toggleBtn');
let isActive = false;

toggleBtn.addEventListener('click', () => {
//Inversion de l'état du bouton
isActive = !isActive;

// Changement visuel
/*A NOTER QU' : avec la valeur false cela induit à la fonction toggle de supprimer le style 
css correspondant et dans le cas contraire true indiquant à la fonction toggle d'ajouter le style
css correspondant*/

toggleBtn.classList.toggle('bg-green-500', isActive);
toggleBtn.classList.toggle('bg-gray-300', !isActive);

const knob = toggleBtn.querySelector('span');
knob.classList.toggle('translate-x-1', !isActive);
knob.classList.toggle('translate-x-6', isActive);

// Action backend (ex: POST vers Express)
fetch('/api/arrosage', {  
    //Method : POST pour indiquer un envoi de données
    method: 'POST',
    //Dans content-Type pour indiquer que le corps de la requête est en JSON
    headers: { 'Content-Type': 'application/json' },
    //JSON.stringify pour convertir l'objet JS en chaîne JSON
    body: JSON.stringify({ active: isActive })
    //La fonction then pour gérer d'avance la réponse qui sera reçue du serveur
}).then(res => res.json())
    .then(data => console.log('Réponse serveur:', data))
    //La fonction catch pour gérer les erreurs potentielles en liaison avec la requête fetch 
    //C'est-à-dire si une erreur se produit lors de l'envoi de la requête ou de la réception de la réponse provenant du serveur
    .catch(err => console.error('Erreur:', err));
});
