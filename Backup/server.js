const http = require('http');

/*
// Importer l'application Express et la fonction setWebSocket depuis app.js
// Cela dit tout ce qu'il y a dans app.js sera stocké et disponible dans les
// constantes app et setWebSocket dans ce fichier server.js

// Pour nous permettre ainsi d'appeler la fonction setWebSocket définie dans app.js
// pour donner la valeur de wss (serveur websocket) dans app.js
*/
const { app, setWebSocket } = require('./app');

const webSocket = require('ws');

/*
// Créez un serveur HTTP
// server est l'objet d'instance du serveur HTTP

// L'argument app dans la fonction createServer permet de dire 
// "utilise l'application Express app pour gérer les requêtes HTTP
// qui arrivent sur ce serveur"
*/
const server = http.createServer(app);

/*
// Créez un serveur WebSocket
// wss est l'objet d'instance du serveur websocket

// L'argument { server } dans la fonction webSocket.Server permet 
// de greffer le serveur websocket au serveur HTTP créé juste avant
// pour qu'ils puissent partager le même port d'écoute
*/
const wss = new webSocket.Server({ server });

/*
// Passez l'objet wss à app.js
// Cela permet d'appeler la fonction setWebSocket définie dans app.js
// pour donner la valeur de wss (serveur websocket) dans app.js

// A savoir que wss stocke la collection/ la liste de tous les clients websocket connectés
*/
setWebSocket(wss); 

// Ecoutez les connexions WebSocket

/*
wss est un objet stockant une collection/une liste de tous les clients (nouveau(qui vient
de se connecter au site via son navigateur) et ancien(déjà connecté au site)
*/

//ws est l'objet représentant le client websocket individuel (nouveau client qui vient de se connecter)

/*
//Ce code permet de déclencher une fonction fléchée à chaque fois 
//qu'un nouveau client se connecte au serveur websocket 

//A noter que wss ici est un objet qui reprèsente le serveur websocket
//et la méthode "on" permet d'écouter un évènement spécifique à savoir "connection" du nouveau client
*/
wss.on('connection', (ws) => {
  console.log('Client WebSocket connecté');

    //Pour envoyer un message de retour (dans le terminal) à l'utilisateur connecté uniquement 
    //lorsque le port côté serveur du websocket à intercepter un message du client
    ws.on('message', (message) => {
    console.log('Message reçu du client:', message);
    });

    // Vous pouvez traiter le message ici et envoyer une réponse si nécessaire
    ws.send(`Message reçu: ${message}`);
});

// Démarrez le serveur
server.listen(process.env.PORT || 3000);