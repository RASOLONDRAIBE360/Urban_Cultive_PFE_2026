const express = require('express');
const { SerialPort } = require('serialport');
//Importation de la bibliothèque SerialPort pour communiquer avec l'Arduino via le port série.
const app = express();
const port = new SerialPort({ path: 'COM5', baudRate: 9600}); 

//Importation de l'outil pour lire les données reçues via l'arduino ligne par ligne
const { ReadlineParser } = require('@serialport/parser-readline');

//Déclaration d'un objet parser pour écouter et lire les données reçues via le port série connecté à l'Arduino
const parser = port.pipe(new ReadlineParser({ delimiter: '\r\n' }));

// Déclaration de la variable wss pour le serveur WebSocket
let wss; 

/*
Ici, on instancie un objet à partir de la bibliothèque SerialPort que nous avons importée.
On configure le port série sur lequel l'objet va communiquer : il s'agit du port COM5,
où l'Arduino est connecté. La vitesse de transmission des données est fixée à 9600 bauds.
*/
app.use((req, res, next) => {

  res.setHeader('Access-Control-Allow-Origin', '*');

  res.setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content, Accept, Content-Type, Authorization');

  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
  next();
});

parser.on('data', (data) => {
  console.log('Donnée reçue :', data); // ← "123.45"
});

/*
// Envoyez les données à tous les clients WebSocket connectés
// Ici, la condition if(wss) permet de vérifier si le serveur websocket est bien instancié
// avant d'essayer d'envoyer des données aux clients connectés
// Autrement dit, si le serveur websocket a bien été déclaré et instancié dans le fichier server.js
*/

/*
// Pour permettre de donner la valeur de wss (serveur websocket) depuis le fichier server.js
// Autrement dit, pour passer la liste des clients connectés au serveur websocket
*/
function setWebSocket(server){
  wss = server;
}

if (wss) {
  wss.clients.forEach((client) => {
    if (client.readyState === WebSocket.OPEN) {
      client.send(data); // Envoyer les données reçues
    }
  });
}

app.get('/led/on', (req, res) => {
  port.write('1');
  // Envoyer le caractère '1' à l'Arduino pour allumer la LED
  /*La fonction write appelé par l'objet d'instanciation serialport permet
  d'envoyer des données sur l'Arduino*/
  res.send('LED allumée');
});

app.get('/led/off', (req, res) => {
  port.write('0');
  res.send('LED éteinte');
});

module.exports = {app, setWebSocket};