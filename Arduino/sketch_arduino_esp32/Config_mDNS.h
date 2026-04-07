/*
  Sert à étiquetté mon arduino sur le réseau peu 
  importe l'ip adresse qui lui sera attribuer par le service DHCP.
  Cela dit mon arduino ne sera pas désigner par son ip adresse (qui varie souvent)
  mais plutôt par son étiquette.
*/

void config_mDNS(){

  if(!MDNS.begin("mon_arduino_esp32")){ // On choisit le nom  ici
    Serial.println("Erreur mDNS !");
  } else {
    Serial.println("mDNS demarre : mon_arduino_esp32.local");
  }

}
