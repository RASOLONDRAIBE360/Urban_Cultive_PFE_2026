IPAddress local_IP(11, 0, 0, 115); //L'IP que vous voulez 
IPAddress gateway(11, 0, 0, 1); //Doit être le passerelle par défaut qui figure dans le dossier de la commande "ipconfig"
IPAddress subnet(255, 0, 0, 0);

// -- MES INFOS WI-FI ---
const char* ssid = "Robotix"; 
const char* password = "12345678";//"01234567"; //"12345678";