/************ VARIABLE POUR SUIVRE L'ETAT DE L'ACTION (ACTIVATION OU DESACTIVATION) SUR LA POMPE *************/
bool success_led_on = false; 
bool success_led_off = false;
/************ VARIABLE POUR SUIVRE L'ETAT D'UNE ACTION (ACTIVATION OU DESACTIVATION) SUR LA POMPE *************/

/******************** GESTION DU TEMPS (NON-BLOQUANT) ********************/
// Pour capturer la dernière fois où les données capteur a été envoyé vers l'ESP32 
// pour éviter l'envoie en continue des données de capteur dans le buffer de l'ESP32
// ce qui bloquerais l'ESP32 à se focaliser que sur cette tâche spécifique et à délâcher
// son attention sur les autres tâches telle que : l'écoute de toute éventuelle message (document json)
// qui pourrait être renvoyé par flask via la communication MQTT
unsigned long derniereEnvoieDonneeCapteur = 0;

// Pour définir la durée d'attente avant la prochaine envoie des données de capteurs vers l'ESP32
const unsigned long dureeAttenteEnvoie = 2000; // 3 secondes 
/******************** GESTION DU TEMPS (NON-BLOQUANT) ********************/

/******** MESSAGE BUFFER ASYNCHRONE **********/
String inputBufferESP32 = "";
/******** MESSAGE BUFFER ASYNCHRONE **********/

/********  GESTION DES BROCHES MODULE RELAIS  *********/
const int pinRelais_1 = 9;
const int pinRelais_2 = 10;
const int pinRelais_7 = 11;
const int pinRelais_8 = 12; 
/********  GESTION DES BROCHES MODULE RELAIS  *********/

/******** RECONFIG DES PORT SERIAL (RX et TX) SUR L'ARDUINO UNO ********/
// RX = 10 | TX = 11
//SoftwareSerial espSerial(10, 11);
/******** RECONFIG DES PORT SERIAL (RX et TX) SUR L'ARDUINO UNO ********/

/********  GESTION DES BROCHES POUR BH1750  *********/
float lux = 0;
#define BH1750_ADDR 0x23 //Adresse du multiplexeur à qui nous allons communiquer pour récupérer les données des capteurs BH1750
/*
  On crée un objet nommé "lightMeter" (notre posemètre). Pour avoir accès 
  aux fonctions qui nous permettra de manipuler le capteur de luminosité.
*/
BH1750 lightMeter1;
BH1750 lightMeter2;
BH1750 lightMeter3;
BH1750 lightMeter4;

const int pinS0 = 36;
const int pinS1 = 38;
const int pinS2 = 40;
/********  GESTION DES BROCHES POUR BH1750  *********/

/********  GESTION DES BROCHES POUR DHT11  *********/
/*
  La pin GPIO 32 sur l'esp32 fait partie du groupe ADC1 nécessaire pour la conversion
  du signal analogique en signal numérique. Et pour sa spécificité les groupes ADC1 sont
  plus stable en lecture de valeur de donnée des capteurs (peut fonctionner en parallèle
  avec le wifi)  
*/
#define pinDHT1 4 // parcelle 1
#define pinDHT2 5 //parcelle 2
#define pinDHT3 6 // parcelle 3
#define pinDHT4 7 // parcelle 4

/*
  Configuration de l'objet DHT côté arduino pour indiquer le "pin" sur lequel il est branché 
  ainsi que le "type" de ce capteur.
*/
DHT dht1(pinDHT1, DHT11);
DHT dht2(pinDHT2, DHT11);
DHT dht3(pinDHT3, DHT11);
DHT dht4(pinDHT4, DHT11);
/********  GESTION DES BROCHES POUR DHT11  *********/

/********  GESTION DES BROCHES POUR RAINDROP SENSOR  *********/
int pin_RaindropSensor_1 = A0;
int pin_RaindropSensor_2 = A1;
int pin_RaindropSensor_3 = A2;
int pin_RaindropSensor_4 = A3;

extern int valueRaindropSensor;
int valueRaindropSensor = 0;

extern int mappedValueRaindropSensor;
int mappedValueRaindropSensor = 0;
/********  GESTION DES BROCHES POUR RAINDROP SENSOR  *********/