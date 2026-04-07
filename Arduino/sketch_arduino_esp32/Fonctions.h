/*************************  CONFIG WiFi  *******************************/
void config_WiFi() {
  //1. Connexion au Wi-Fi
  /*
    Configuration du mode de connexion de l'ESP32 au wifi afin qu'il se comporte comme étant 
    le client.

    L'ESP32 agit comme un client qui se connecte à un routeur ou point d'accès existant (box Internet,
    smartphone en partage de connexion, etc.).
  */
  WiFi.mode(WIFI_STA);

  if(!WiFi.config(local_IP, gateway, subnet)){
    Serial.println(F("Erreur de configuration de l'IP Fixe"));
  }
  
  //Pour établir la connexion entre mon ESP32 et le wifi
  WiFi.begin(ssid, password);

  //Condition pour vérifier si la connexion de l'ESP32 avec le Wifi a bien été établie 
  /*
    Redémarrer le lancement du programme de l'ESP32 tant que sa connexion avec la wifi n'a pas été bien établie
  */
  while(WiFi.waitForConnectResult() != WL_CONNECTED){
    Serial.println(F("Connection au Wi-Fi échouée ! Rebooting..."));
    delay(5000);
    ESP.restart(); //Commande pour forcer le démarrage de mon esp32
  }

  Serial.print(F("Connecté ! Allez sur http://"));
  Serial.println(WiFi.localIP());

  //Serial.println("Prêt pour le téléversement sans fil !");

  WiFi.setSleep(false);
}
/*************************  CONFIG WiFi  *******************************/

/*************************  CONFIG CONNEXION MQTT  ***************************/
void config_server_mqtt(){
  client.setServer(mqtt_server, mqtt_port);
}

void reconnect() {
    // On ne fait rien si on est déjà connecté
    if (client.connected()) {
      return;
    }

    // On tente une reconnexion toutes les 5 secondes SANS bloquer le reste
    unsigned long now = millis();

    if (now - lastReconnectAttempt > 5000) {
      lastReconnectAttempt = now;
      
      Serial.print(F("Tentative de connexion MQTT..."));
    
      if (client.connect("ESP32Client")) {
        Serial.println(F("Connecté !"));

        for (int i = 0; i < num_topics; i++){
          // L'inscription au topic "/led/on" ou "/led/off" pour récupérer tout éventuelle info provenant du serveur flask se fera juste après 
          // que la connexion sur le wifi et le brocker MQTT sera bien établie
          if (client.subscribe(topics_to_subscribe[i])){
            Serial.print(F("Abonné au topic : "));
            Serial.print(topics_to_subscribe[i]);
            Serial.println(F(" du serveur Flask"));

          } else {
            Serial.print(F("Erreur d'abonnement au topic : "));
            Serial.println(topics_to_subscribe[i]);
          }
        }

      } else {
        Serial.print(F("Échec, rc="));
        Serial.print(client.state());
        Serial.println(F(" nouvelle tentative dans 5 secondes"));
      }

    }
}
/*************************  CONFIG CONNEXION MQTT  ***************************/

/*************** RECUPERATION DE LA COMMANDE DE MANIPULATION DE LA LED VIA LA COMMUNICATION MQTT ****************/
void callback(char* topic, byte* payload, unsigned int length){
  // 1. Décodage du JSON envoyé par flask
  JsonDocument doc;

  // Ici nous allons stocké le contenu du document json dans notre variable "doc" à l'aide de la fonction
  // "deserializeJson".
  // length -> c'est la taille du texte JSON (nombre de caractère/bytes). ça permet à la fonction "deserializeJson" de savoir 
  // jusqu'où lire dans la variable payload.
  // payload -> c'est le texte brut JSON (envoyé via la communication MQTT)
  // doc -> c'est notre conteneur JSON (JsonDocument) qui va recevoir les données parsées
  DeserializationError error = deserializeJson(doc, payload, length);

  //La fonction deserializeJson(..) retourne un objet de type DeserializationError
  // Si le stockage réaliser par la fonction "deserializeJson(..)" a réussi alors elle ne renvoie rien. La variable "error" sera vide. if(error) -> faux
  // Si le stockage réaliser par la fonction "deserializeJson(..)" a rencontré une erreur quelconque. La variable "error" va contenir une sorte de
  // "code d'état" telle que : InvalidInput, NoMemory, etc. if(error) -> vrai
  if (error) return;

  // 2. Extraction des données
  String commande = doc["commande"]; // "ON" ou "OFF"

  // JsonArray est utilisé ici pour faire l'interprétation/casting du contenu en format Json -> en format Array (une liste de donnée manipulable en Arduino)
  // ex : si de base nous avons un document json suivant : {"list_id_parc": [1, 2, 3]}
  // Après avoir appliqué JsonArray list_id_parc = doc["list_id_parc"].as<JsonArray>(). Nous faisons l'interprétation de ce document Json en liste de donnée manipulable sur Arduino comme telle :
  // 1
  // 2
  // 3
  JsonArray list_id_parc = doc["list_id_parc"].as<JsonArray>();

  // 3. Envoie de la commande à l'Arduino UNO via mySerial (Liaison RX/TX)
  for(String id_parc : list_id_parc){
    // Format envoyé sur le fil : "ON->OP_001\n"
    mySerial.print(commande);
    mySerial.print(F("->"));
    mySerial.println(id_parc); // CRUCIAL : println ajoute le '\n' attendu par readStringUntil côté UNO

    Serial.print(F("Transmis à l'Arduino : "));
    Serial.print(commande);
    Serial.print(F(" pour "));
    Serial.println(id_parc);
  }

}
/*************** RECUPERATION DE LA COMMANDE DE MANIPULATION DE LA LED VIA LA COMMUNICATION MQTT ****************/

/*************** RECUPERATION DES DONNEES CAPTEUR ENVOYER PAR L'ARDUINO UNO ****************/
void recuperate_send_data_sensor_to_flask(String trame_info_sensor){
  // Pour nettoyer le texte reçu de l'Arduino UNO (contenant les info sur le topic ainsi que la valeur des capteurs).
  // Le nettoyage ici s'agit de la suppression des espaces qui peut y avoir avant et après le texte reçu pour mieux faciliter le processus d'extraction
  trame_info_sensor.trim();

  // Ici trame_info_sensor.length() > 5 est là pour s'assurer qu'il y a bien une valeur qui suit le texte "DATA:" et non pas qu'il est vide pour éviter tout
  // éventuel erreur d'extraction des données 
  if (trame_info_sensor.length() == 0) return;
  // On vérifie si c'est une donnée capteur qui a été envoyé par l'arduino UNO ou bien un simple texte informatif
  if (trame_info_sensor.length() > 5 && trame_info_sensor.startsWith("DATA:")){

    int posFleche = trame_info_sensor.indexOf("->");

    if (posFleche != -1){
      // On commence l'extraction à l'index 5 pour sauter "DATA:" et directement récupérer la valeur du topic 
      String topicMqtt = trame_info_sensor.substring(5, posFleche);
      String sensorValue = trame_info_sensor.substring(posFleche + 2);

      // Extraction du type du capteur (ex : temperature, luminosite ou humidite)
      // int premierSep va contenir la valeur d'index du premier "/" dans le texte stocker dans "topicMqtt"
      int premierSep = topicMqtt.indexOf("/");

      // int secondSep va contenir l'index du second "/" dans le texte stocker dans "topicMqtt"
      // La première argument dans la fonction indexOf("/", ..) sert à indiquer à l'ordinateur le symbole qui est à rechercher
      // dans le texte stocker dans "topicMqtt". Tandis que le second argument indexOf(.., premierSep + 1) sert à indiquer l'index
      // où la recherche du "/" va commencer. Dans notre cas, premierSep + 1 -> 4 + 1 = 5
      int secondSep = topicMqtt.indexOf("/", premierSep + 1);

      if (premierSep != -1 && secondSep != -1){
        // On récupère ce qu'il y a entre les deux slashes
        String sensorType = topicMqtt.substring(premierSep + 1, secondSep);

        // Construction du document JSON à envoyer vers flask et notre interface streamlit pour récupération des données capteur
        // Format voulu : {"temperature": 25.5} ou {"luminosite": 120}
        String jsonPayload = "{\"" + sensorType + "\": " + sensorValue + "}";

        if (topicMqtt.length() > 0 && sensorValue.length() > 0){
          //Publication via la communication MQTT des info capteur récupérer 
          client.publish(topicMqtt.c_str(), jsonPayload.c_str());

          Serial.print(F("Relais MQTT (Json envoyé) : \n"));
          Serial.println(jsonPayload.c_str());
        } else {
          Serial.print(F("Erreur survenu lors de l'extraction des infos capteur -> topicMqtt et sensorValue"));
        }
      
      }

    }
  }
}
/*************** RECUPERATION DES DONNEES CAPTEUR ENVOYER PAR L'ARDUINO UNO ****************/

// 3. La fonction de traitement (déportée pour la clarté)
void processUnoMessage(String msg) {
  msg.trim();
  if (msg.length() == 0) return;

  if (msg.startsWith("{\"Status_code\":")) {
    if (client.connected()) {
      client.publish("action/led/status", msg.c_str());
    }
    Serial.print(F("Status transmis à Flask : "));
    Serial.println(msg);
  } else {
    // C'est une donnée capteur (ex: DATA:data/temp->25)
    recuperate_send_data_sensor_to_flask(msg);
  }
}
