// --- Config MQTT ---
/*
  mqtt_server = "broker.emqx.io" 
  -> définit l'adresse du serveur MQTT (le broker).
  Le broker reçoit les messages envoyés par le "publisher"
  (exemple : données d'un capteur sur l'ESP32/Arduino)
  et les redistribue aux "subscribers" abonnés au même topic.
*/
const char* mqtt_server = "10.119.163.221";//"10.47.121.221"; //"11.0.0.116";//"192.168.100.117"; // C'est l'ip de mon pc qui est utilisé comme hôte du serveur mosquitto (le serveur broker) pour assurer la communication sous protocole MQTT
const int mqtt_port = 1883;

/*
  topic_lux = "data/luminosite/" 
  -> topic_lux est une variable qui contient le nom du topic MQTT
*/
/*
  const char* topic_lux = "data/luminosite/";
  const char* topic_dht11 = "data/temperature/";
  const char* topic_dht11 = "data/humidite/";
*/

/*
  Configuration pour spécifier à mon client "publisher" et "subscriber" la connexion wifi qui sera utiliser pour assurer la communication MQTT
*/
WiFiClient espClient;
PubSubClient client(espClient);