IPAddress local_IP(10, 119, 163, 115);//(10, 47, 121, 125);//(11, 0, 0, 50);//(192, 168, 100, 125); //L'IP que vous voulez 
IPAddress gateway(10, 119, 163, 127);//(10, 47, 121, 19); //(11, 0, 0, 1); //Doit être le passerelle par défaut qui figure dans le dossier de la commande "ipconfig"
IPAddress subnet (255, 255, 255, 0); //(255, 0, 0, 0);

// -- MES INFOS WI-FI ---
const char* ssid = "Bryan";//"Bryan"; //"Robotix-5G";//"Telecom-5R8G";
const char* password = "01234567";//"01234567"; //"12345678";//"bss28iet";