//----------------------- CONFIG TOPIC DHT11 ---------------------------
struct data_dht11 {
  const char* id_parc; // Exemple: "OP_001"
  DHT* dht;       // Pointeur vers l'objet DHT correspondant
  const char* topic;   // Topic MQTT spécifique
};

// Initialisation de la liste de structure de donnée
data_dht11 myTopic_dht11[] = {
  {"OP_001", &dht1, "data/temperature/OP_001"},
  {"OP_002", &dht2, "data/temperature/OP_002"},
  {"OP_003", &dht3, "data/temperature/OP_003"},
  {"OP_004", &dht4, "data/temperature/OP_004"},
};

// On calcule automatiquement le nombre de parcelles dans la liste
const int nbData_dht11 = sizeof(myTopic_dht11) / sizeof(myTopic_dht11[0]);
//----------------------- CONFIG TOPIC DHT11 ---------------------------

//----------------------- CONFIG TOPIC BH1750 ---------------------------
struct data_bh1750 {
  const char* id_parc;        // Exemple: "OP_001"
  BH1750* lightMeter;    // Pointeur vers l'objet BH1750 correspondant
  const char* topic;          // Topic MQTT spécifique
  uint8_t canal;         // valeur entier pour indiquer la position de décallage du bit active afin d'activer le canal cible du multiplexeur (0 à 7)
};

// Initialisation de la liste de structure de donnée
data_bh1750 myTopic_bh1750[] = {
  {"OP_001", &lightMeter1, "data/luminosite/OP_001", 0},
  {"OP_002", &lightMeter2, "data/luminosite/OP_002", 1},
  {"OP_003", &lightMeter3, "data/luminosite/OP_003", 2},
  {"OP_004", &lightMeter4, "data/luminosite/OP_004", 3},
};

// On calcule automatiquement le nombre de donnée dans la liste
const int nbData_bh1750 = sizeof(myTopic_bh1750) / sizeof(myTopic_bh1750[0]);
//----------------------- CONFIG TOPIC BH1750 ---------------------------

//----------------------- CONFIG TOPIC RAINDROP SENSOR ---------------------------
struct data_raindrop_sensor {
  const char* id_parc;
  int pin_RaindropSensor;
  const char* topic;
};

data_raindrop_sensor myTopic_raindrop_sensor[] = {
  {"OP_001", pin_RaindropSensor_1, "data/humidite/OP_001"},
  {"OP_002", pin_RaindropSensor_2, "data/humidite/OP_002"},
  {"OP_003", pin_RaindropSensor_3, "data/humidite/OP_003"},
  {"OP_004", pin_RaindropSensor_4, "data/humidite/OP_004"},
};

const int nbData_raindrop_sensor = sizeof(myTopic_raindrop_sensor) / sizeof(myTopic_raindrop_sensor[0]);
//----------------------- CONFIG TOPIC RAINDROP SENSOR ---------------------------

