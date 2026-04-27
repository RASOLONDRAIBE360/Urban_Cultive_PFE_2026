/********  GESTION DU TEMPS DE CONNEXION  *********/
unsigned long lastReconnectAttempt = 0;
/********  GESTION DU TEMPS DE CONNEXION  *********/

/********  GESTION TIMING  *********/
String inputBuffer = "";

// Variable permettant de limiter le nombre de caractère qui sera lue par l'ESP32. Pour éviter qu'il ne perd pas de 
// vue la vérification de toute éventuelle information qui peut être envoyé par flask via la communication MQTT à travers le wifi
int limiteNombreCaractere = 0;
/********  GESTION TIMING  *********/

/********  CONFI POUR UTILISATION DE LA COMMUNICATION UART2 AFIN D'ASSURER LA COMMUNICATION ENTRE L'ESP32 ET L'ARDUINO UNO *******/
HardwareSerial mySerial(2);
/********  CONFI POUR UTILISATION DE LA COMMUNICATION UART2 AFIN D'ASSURER LA COMMUNICATION ENTRE L'ESP32 ET L'ARDUINO UNO *******/

/*************** GESTION DES TOPIC D'ABONNEMENT PAR LE CLIENT : pour adaptation dynamique du message d'info dans le buffer **************/
// Liste de tes topics d'abonnement
const char* topics_to_subscribe[] = {
  "/led/on",
  "/led/off"
};

// Calcul automatique du nombre de topics
const int num_topics = sizeof(topics_to_subscribe) / sizeof(topics_to_subscribe[0]);
/*************** GESTION DES TOPIC D'ABONNEMENT PAR LE CLIENT : pour adaptation dynamique du message d'info dans le buffer **************/