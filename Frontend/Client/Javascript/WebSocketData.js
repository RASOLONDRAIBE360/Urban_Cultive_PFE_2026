// Établir une connexion WebSocket
const socket = new WebSocket('ws://localhost:3000');

// Écouter les messages du serveur
socket.onmessage = (event) => {
    const dataContainer = document.getElementById('data-container');
    const newData = document.createElement('p');
    newData.textContent = event.data;
    dataContainer.appendChild(newData);
};

// Gérer les erreurs
socket.onerror = (error) => {
    console.error('Erreur WebSocket :', error);
};

// Gérer la fermeture de la connexion
socket.onclose = () => {
    console.log('Connexion WebSocket fermée');
};