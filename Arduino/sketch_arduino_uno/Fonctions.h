/*************************  ALLUMAGE LED  *******************************/
bool allumerLed(String id_parc){

  if (id_parc == "OP_001"){

    digitalWrite(pinRelais_1, LOW);
    return true;

  } else if (id_parc == "OP_002"){

    digitalWrite(pinRelais_2, LOW);
    return true;

  } /*else if (id_parc == "OP_003"){

    digitalWrite(pinRelais_7, LOW);
    return true;

  } else if (id_parc == "OP_004"){

    digitalWrite(pinRelais_8, LOW);
    return true;

  }*/

  return false; // Echec : l'ID de parcelle n'existe pas
}
/*************************  ALLUMAGE LED  *******************************/

/*************************  ETEINDRE LED  *******************************/
bool eteindreLed(String id_parc){
  
  if(id_parc == "OP_001"){

    digitalWrite(pinRelais_1, HIGH);
    return true;

  } else if (id_parc == "OP_002"){

    digitalWrite(pinRelais_2, HIGH);
    return true;

  } /*else if (id_parc == "OP_003"){

    digitalWrite(pinRelais_7, HIGH);
    return true;

  } else if (id_parc == "OP_004"){

    digitalWrite(pinRelais_8, HIGH);
    return true;

  }*/

  return false; // Echec : l'ID de parcelle n'existe pas
}
/*************************  ETEINDRE LED  *******************************/

/************* RECUPERATION INFO ENVOYER PAR L'ARDUINO ESP32 ************/
// Obligation d'utilisation du type "HardwareSerial" et de la référence "&" pour passer l'objet Serial en paramètre.
// Sans cela, l'Arduino va tenter de copier l'objet, ce qui provoquera une erreur de compilation car l'objet "Serial"
// ne peut pas être copié
void recuperate_data_send_esp32(String msg){  
  // NETTOYAGE CRUCIALE
  // On remet à zéro pour ce nouvel ordre. POURQUOI ?
  // Ex : Si Lundi : On allume la pompe1. success_led_on devient "true"
  // Mardi : On veut l'éteindre. On envoie donc l'ordre "OFF". L'Arduino l'éteint
  // bien et met success_led_off à "true".
  // Le crash : Mais l'Arduino e souvient encore que success_led_on était "true" hier !
  // Conséquence : Il va envoyer à l'ESP32 -> "DEUX MESSAGES JSON en même temps":
  // Un pour dire "J'allume" (le vieux succès d'hier) et un pour dire "j'éteins" (le succès d'aujourd'hui).
  // -> Sans la réinitialisation des deux variables ci-dessous : l'Arduino va recevoir un "ON" en plein milieu d'un "OFF".
  // L'objectif ici : c'est de dire à l'Arduino -> "Oublie tout ce que tu as fait avant de commencer à lire un nouvel ordre".
  success_led_on = false;
  success_led_off = false;

  // Nettoyage du message reçu par l'arduino ESP32 (enlève les espaces/retours chariot)
  msg.trim(); 

  // On cherche la position de la flèche séparateur entre la commande "ON" et l'id_parc "OP_001" (à titre d'exemple)
  int posFleche = msg.indexOf("->");

  if(posFleche != -1){
    // On extrait ce qui est AVANT la flèche (pour récupérer la commande)
    // La fonction "substring" sert à découper une chaîne de caractère
    // 0 -> pour indiquer la valeur de départ du découpage (c'est-à-dire la toute première lettre dans la chaîne de caractère à découper) 
    // posFleche -> pour indiquer la valeur de fin du découpage (c'est-à-dire l'index de position du caractère "-" dans "->")
    String commande = msg.substring(0, posFleche);

    // On extrait ce qui est APRES la flèche (pour récupérer l'id_parc)
    // On ajoute +2 puisque dans la chaîne de caractère "ON->OP_001" (à titre d'exemple la valeur de l'id_parc), la position du caractère
    // "-" est 2 donc (posFleche + 2) -> 2 + 2 = 4. Cela dit 4 c'est la position de la lettre 'O'. Ici O sera prise en considération et non
    // pas ignorer comme valeur de départ du découpage pour pouvoir ainsi récupérer la valeur de l'id_parc (qui se trouve juste après le caractère de séparation "->")
    String id_parc = msg.substring(posFleche + 2);

    Serial.print(F("Commande reçue : [")); 
    Serial.print(commande); 
    Serial.print(F("] pour la parcelle : [")); 
    Serial.print(id_parc);
    Serial.print(F("]"));

    if(commande == "ON"){
      success_led_on = allumerLed(id_parc);
    } else if(commande == "OFF"){
      success_led_off = eteindreLed(id_parc);
    }

    if (success_led_on){
      // Si l'action a réussi (ID trouvé et pin activé)
      if (id_parc == "OP_001"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_001\", \"Action\": \"ON\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_001\", \"Action\": \"ON\"}"));

      } else if (id_parc == "OP_002"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_002\", \"Action\": \"ON\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_002\", \"Action\": \"ON\"}"));

      } else if (id_parc == "OP_003"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_003\", \"Action\": \"ON\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_003\", \"Action\": \"ON\"}"));

      } else if (id_parc == "OP_004"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_004\", \"Action\": \"ON\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_004\", \"Action\": \"ON\"}"));

      }

      Serial.println(F("Action d'activation pompe reussie: 200 envoyé à l'ESP32"));

    }

    if (success_led_off){
      // Si l'action a réussi (ID trouvé et pin activé)
      if (id_parc == "OP_001"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_001\", \"Action\": \"OFF\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_001\", \"Action\": \"OFF\"}"));

      } else if (id_parc == "OP_002"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_002\", \"Action\": \"OFF\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_002\", \"Action\": \"OFF\"}"));

      } else if (id_parc == "OP_003"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_003\", \"Action\": \"OFF\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_003\", \"Action\": \"OFF\"}"));

      } else if (id_parc == "OP_004"){
        espSerial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_004\", \"Action\": \"OFF\"}"));
        Serial.println(F("{\"Status_code\": 200, \"Id_parc\": \"OP_004\", \"Action\": \"OFF\"}"));

      }

      Serial.println(F("Action de désactivation pompe reussie: 200 envoyé à l'ESP32"));

    }

  }
}
/************* RECUPERATION INFO ENVOYER PAR L'ARDUINO ESP32 ************/

/********* FONCTION POUR VERIFIER TOUTE EVENTUELLE MESSAGE QUI PEUT ETRE ENVOYER PAR L'ESP32 **********/
void ecouter_ESP32() {
  // LECTURE NON-BLOQUANTE DE L'ESP32 SANS readStringUntil()
  while(espSerial.available() > 0) {
    char c = espSerial.read();
    
    if (c == '\n') { // On a reçu la fin de la commande
      recuperate_data_send_esp32(inputBufferESP32);
      Serial.print(F("Reçu de l'ESP32 : "));
      Serial.println(inputBufferESP32);
      inputBufferESP32 = ""; // On vide pour le suivant
    } else {
      inputBufferESP32 += c; // On accumule
    }
  }
}
/********* FONCTION POUR VERIFIER TOUTE EVENTUELLE MESSAGE QUI PEUT ETRE ENVOYER PAR L'ESP32 **********/

//*************** ENVOYER DES INFO CAPTEUR VERS L'ESP32 *****************/
// &sensor pour dire à l'arduino de ne plus créer un nouveau capteur, mais d'utiliser celui que l'on lui a déjà donné
// En ayant spécifier le type "uint8_t" pour la variable i nous nous assurons juste que sa valeur doit être positive et comprise dans l'intervalle de [0 - 255]
void tcaselect(uint8_t i) {
  // Sélection manuelle des canaux par signaux digitaux
  if (i == 0) { 
    // Canal Y0 : Code binaire 000
    digitalWrite(pinS0, LOW); 
    digitalWrite(pinS1, LOW);  
    digitalWrite(pinS2, LOW);
  } 
  else if (i == 1) { 
    // Canal Y1 : Code binaire 001
    digitalWrite(pinS0, HIGH); 
    digitalWrite(pinS1, LOW);  
    digitalWrite(pinS2, LOW);
  } 
  else if (i == 2) { 
    // Canal Y2 : Code binaire 010
    digitalWrite(pinS0, LOW);  
    digitalWrite(pinS1, HIGH); 
    digitalWrite(pinS2, LOW);
  } 
  else if (i == 3) { 
    // Canal Y3 : Code binaire 011
    digitalWrite(pinS0, HIGH); 
    digitalWrite(pinS1, HIGH); 
    digitalWrite(pinS2, LOW);
  }
}

// const char* topic mise en paramètre sert à récupérer l'adresse en mémoire directe 
//du texte qui s'y trouve sans ni refaire une allocation de mémoire ni une copie de 
//la valeur qui est déjà stocké pour ne pas consommer trop de mémoire
void send_data_sensor_to_esp32(const char* topic, float sensorValue){
  // On écoute s'il y a une urgence juste avant d'envoyer
  ecouter_ESP32();

  if (strlen(topic) == 0 || topic == NULL){
    Serial.println(F("ERREUR : Le topic est vide !"));
    return; // On n'envoie rien si le topic est inexistant
  }

  // On ajoute "DATA:" pour faciliter l'extraction côté ESP32
  espSerial.print("DATA:");
  espSerial.print(topic);
  espSerial.print("->");
  espSerial.println(sensorValue);

  Serial.print(F("Envoyé à l'ESP32 : DATA:"));
  Serial.print(topic);
  Serial.print(F("->"));
  Serial.println(sensorValue);

  // DELAI INTELLIGENT : Au lieu d'un delay(50) qui rend l'UNO sourd et rate des messages,
  // nous utilisons un délai qui lit le port en continu !

  // L'assignation de debutChronoEnvoi = millis() est executé strictement une seule fois. Et une fois qu'on
  // entre dans le boucle while on n'y sort plus tant que le temps n'est pas écoulé
  debutChronoEnvoi = millis();

  while(millis() - debutChronoEnvoi < 50) {
    ecouter_ESP32();
  }
}
//*************** ENVOYER DES INFO CAPTEUR VERS L'ESP32 *****************/

/*********************** RECUPERATION DONNEE CAPTEUR ********************/
void recuperate_data_sensor_dht11(){
  for(int i = 0; i < nbData_dht11; i++){
    float temp = myTopic_dht11[i].dht->readTemperature();

    if (isnan(temp)){
      // POURQUOI NOUS EST-IL IMPOSSIBLE D'UTILISER L'OPERATION "+" POUR CONCATENER ICI ?
      // Le "const char*" (Pointeur) : ce n'est pas du texte, c'est juste une adresse mémoire (un numéro de case)
      // Le String (Objet) compte à lui, c'est un outil intelligent. Quand on fais "+", elle sait qu'elle doit aller chercher
      // de la mémoire, agrandir sa taille et coller les deux morceaux ensemble. C'est pratique, mais ça "mange" beaucoup de RAM
      // sur un petit Arduino UNO 
      Serial.print(F("Erreur de lecture du DHT11 pour "));
      Serial.println(myTopic_dht11[i].id_parc);
      return;
    } else {
      send_data_sensor_to_esp32(myTopic_dht11[i].topic, temp);
    }

  }
}

void recuperate_data_sensor_bh1750(){
  for(int i = 0; i < nbData_bh1750; i++){
    tcaselect(myTopic_bh1750[i].canal);

    lux = myTopic_bh1750[i].lightMeter->readLightLevel();

    if (isnan(lux)){
      Serial.print(F("Erreur de lecture du BH1750 pour "));
      Serial.println(myTopic_bh1750[i].id_parc);
      return;
    } else {
      send_data_sensor_to_esp32(myTopic_bh1750[i].topic, lux);
    }

  }
}

void recuperate_data_sensor_raindrop_sensor(){
  for(int i = 0; i < nbData_raindrop_sensor; i++){
    valueRaindropSensor = analogRead(myTopic_raindrop_sensor[i].pin_RaindropSensor);
    mappedValueRaindropSensor = map(valueRaindropSensor, 0, 1023, 255, 0);

    if (isnan(valueRaindropSensor)){
      Serial.print(F("Erreur de lecture du Raindrop Sensor pour "));
      Serial.println(myTopic_raindrop_sensor[i].id_parc);
      return;
    } else {
      send_data_sensor_to_esp32(myTopic_raindrop_sensor[i].topic, mappedValueRaindropSensor);
    }

  }
}
/*********************** RECUPERATION DONNEE CAPTEUR ********************/
