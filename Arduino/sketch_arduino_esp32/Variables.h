/********  GESTION DU TEMPS DE CONNEXION  *********/
unsigned long lastReconnectAttempt = 0;
/********  GESTION DU TEMPS DE CONNEXION  *********/

/********  GESTION TIMING  *********/
unsigned long tempsFin = 0;
bool arrosageActif = false;
String inputBuffer = "";
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