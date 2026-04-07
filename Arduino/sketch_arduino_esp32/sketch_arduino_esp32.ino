// A NOTER QUE : 
// J'utiliserais la fonction F() pour les textes fixes afin d'optimiser la gestion d'utilisation de la RAM.
// Le nombre de caractère qui sont renvoyés dans la mémoire RAM de l'arduino est assez considérable. Ce qui peut provoquer 
// sa saturation (ce qui a été déjà mon cas) -> Tous les Serial.print("Envoyé à l'ESP32...") occupent la RAM. Pour y remédier 
// à ce problème je vais donc utiliser le macro F() forçant ces textes à rester dans la mémoire Flash (32Ko) au lieu de la RAM(2Ko).

#include "Biblio.h"
#include "Config_WiFi.h"
#include "Config_MQTT.h"
#include "Variables.h"
#include "Fonctions.h"

void setup() {

  //Pour initialiser la communication de l'arduino avec l'ordinateur
  /*
    - C’est ce qui permet d’envoyer/recevoir des messages via le Moniteur Série.
  */
  Serial.begin(115200); // Pour mon PC (Debug)

  // Communication Série avec l'Arduino (Vitesse 9600, RX=16, TX=17)
  // A quoi sert la règle de communication SERIAL_8N1 ?
  // C'est comme envoyé une lettre à un ami avec des petites lumières qui clignotent (c'est ça la communication série).
  // 8 -> "Je vais écrire mon mot avec 8 lettres" (en vrai ce sont 8 petits morceaux appelés bits)
  // N -> "Je ne mets pas de correcteur d'orthographe" (pas de parité, donc pas de bit spécial pour vérifier)
  // 1 -> "A la fin de mon mot, je mets un point" (c'est le bit de stop qui dit que le mot est fini).
  
  // Exemple plus illustré :
  // Si on veux envoyer la lettre "A" à un ami:
  // On allumes une lumière pour dire "je commence" (bit de start).
  // On envoies 8 petites lumières qui représentent la lettre A
  // On termine avec une lumière spéciale qui dit "J'ai fini" (bit de stop)
  // L'ami, qui connaît le règle 8N1, sait exactement comment lire mon message
  // RX = 16 | TX = 17 
  mySerial.begin(9600, SERIAL_8N1, 16, 17);

  //mySerial.setRxBufferSize(256);

  // Par défaut, c'est 1000ms, dont l'esp32 a besoin pour pouvoir lire une ligne de donnée ce qui est beaucoup trop long pour mon projet (qui a besoin 
  // d'afficher les données capteurs de manière fluide et près du temps réelle sur l'interface). Pour résoudre cela nous allons diminuer le temps
  //qui sera nécessaire à l'arduino ESP32 pour finir de lire une ligne
  //mySerial.setTimeout(100); // Désormais l'ESP32 n'attendra que 10ms pour finir de lire une ligne de donnée

  Serial.print("Demarrage...");

  config_WiFi();

  config_server_mqtt();

  client.setCallback(callback);
}

void loop(){
  // Condition pour se connecter au broker MQTT (au départ du lancement du programme)
  // Ainsi que vérification de la connexion s'il a été bien établi ou pas
  if(!client.connected()){
    reconnect();
    
  } else {
    // Nécessaire pour maintenir le MQTT vivant
    /*
      Elle sert à maintenir en permanence la connexion de mon client "publisher" avec mon serveur mosquitto
    */
    client.loop();
  }

  // LECTURE NON-BLOQUANTE DE L'UNO
  while (mySerial.available() > 0) {
    char c = mySerial.read(); // On lit 1 seul caractère
    
    if (c == '\n') { // On a reçu la fin de la ligne !
      processUnoMessage(inputBuffer); // On traite le message
      inputBuffer = ""; // On vide pour le suivant
    } else {
      inputBuffer += c; // On accumule le message sans s'arrêter
    }
  }
  
}