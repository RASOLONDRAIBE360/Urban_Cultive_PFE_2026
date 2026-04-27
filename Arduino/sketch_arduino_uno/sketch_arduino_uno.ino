// A NOTER QUE : 
// J'utiliserais la fonction F() pour les textes fixes afin d'optimiser la gestion d'utilisation de la RAM.
// Le nombre de caractère qui sont renvoyés dans la mémoire RAM de l'arduino est assez considérable. Ce qui peut provoquer 
// sa saturation (ce qui a été déjà mon cas) -> Tous les Serial.print("Envoyé à l'ESP32...") occupent la RAM. Pour y remédier 
// à ce problème je vais donc utiliser le macro F() forçant ces textes à rester dans la mémoire Flash (32Ko) au lieu de la RAM(2Ko).

#include "Biblio.h"
#include "Variables.h"
#include "myStruct_Capteur.h"
#include "Fonctions.h"

void setup() {
  //----------------- CONFIG MODULE RELAIS -------------------
  // On utilise Serial (Pins 0 et 1) pour parler avec l'ESP32

  Serial.begin(115200); // Augmentation de la vitesse de récéption et traitement des données reçu de l'ESP32
  Serial2.begin(57600); // Augmentation de la vitesse de transfert vers l'ESP32
  Serial2.setTimeout(100);

  pinMode(pinRelais_1, OUTPUT);
  pinMode(pinRelais_2, OUTPUT);
  pinMode(pinRelais_7, OUTPUT);
  pinMode(pinRelais_8, OUTPUT);

  digitalWrite(pinRelais_1, HIGH);
  digitalWrite(pinRelais_2, HIGH);
  digitalWrite(pinRelais_7, HIGH);
  digitalWrite(pinRelais_8, HIGH);
  //----------------- CONFIG MODULE RELAIS -------------------

  //----------------- CONFIG CAPTEUR BH1750 -------------------
  //Pour initialiser la communication en bus I2C pour être prêt dans l'organisation pour l'envoie et récéption des données de manière synchronisé
  /*
    - Cela prépare les broches SDA/SCL pour la communication synchronisée avec les périphériques I²C (dont ton BH1750).
  */
  Wire.begin(); // 21 (SDA) et 22 (SCL)
  pinMode(pinS0, OUTPUT);
  pinMode(pinS1, OUTPUT);
  pinMode(pinS2, OUTPUT);
  //----------------- CONFIG CAPTEUR BH1750 -------------------

  //----------------- CONFIG CAPTEUR DHT11 -------------------
  dht1.begin();
  dht2.begin();
  dht3.begin();
  dht4.begin();
  //----------------- CONFIG CAPTEUR DHT11 -------------------

  // Initialisation de la communication pour chaque capteur de luminosité
  for (int i = 0; i < nbData_bh1750; i++){
    tcaselect(myTopic_bh1750[i].canal);
    myTopic_bh1750[i].lightMeter->begin(BH1750::CONTINUOUS_HIGH_RES_MODE, BH1750_ADDR);
  }

}

void loop() {
    // ECOUTE PERMANENTE SANS BLOQUER
    ecouter_ESP32();

    // Envoyer les données des capteurs à l'ESP32 seulement quand c'est le moment
    if (millis() - derniereEnvoieDonneeCapteur >= dureeAttenteEnvoie){
      derniereEnvoieDonneeCapteur = millis();
      recuperate_data_sensor_dht11();
      Serial.println("");

      recuperate_data_sensor_bh1750();
      Serial.println("");

      recuperate_data_sensor_raindrop_sensor();
      Serial.println("");
    }

}
