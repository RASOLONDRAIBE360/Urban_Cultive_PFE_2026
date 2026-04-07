import re

filepath = r"c:\xampp\htdocs\Document_PFE\controllers\client\tableau_de_bord.py"
with open(filepath, "r", encoding="utf-8") as f:
    text = f.read()

# 1. Add the import
import_stmt = "from services.client.donnee_capteur_service.recuperate_data_sensor_mqtt import RecuperateDataSensorMQTT\n\n"
text = text.replace("import json\n", "import json\n" + import_stmt)

# 2. Add instantiation after db_config
text = text.replace("arduino_service = ArduinoService()\n", "arduino_service = ArduinoService()\n\nmqtt_service = RecuperateDataSensorMQTT()\n")

# 3. Remove lines 59 to 197 (from ip_arduino = "..." down to def led_on_thread... return status_code \n ######################## THREAD ##########################)
# We can find the start and end tokens
start_idx = text.find('ip_arduino = "192.168.100.125"')
end_idx = text.find('def fonction_led_programmer(ip_arduino, id_parc, duree_arrosage):')

if start_idx != -1 and end_idx != -1:
    # We want to remove the block and replace with a newline
    # Wait, we need to make sure we don't accidentally remove something else.
    # The end_idx is right where the next function begins.
    # To be safe, we'll keep `arduino_service.init_arduino(ip_arduino)`! He deleted `ip_arduino` but `arduino_service.init_arduino(ip_arduino)` is inside the deleted block or not?
    # Ah, `arduino_service.init_arduino(ip_arduino)` is at line 162. So it will be deleted!
    # Instead of deleting raw indices, let's use lines.
    pass

with open(filepath, "r", encoding="utf-8") as f:
    lines = f.readlines()

new_lines = []
skip = False
for i, line in enumerate(lines):
    # Stop condition:
    if line.startswith('ip_arduino ='):
        skip = True
    
    if skip:
        # Before skipping, if it is init_arduino, we keep it but with mqtt_service
        if 'arduino_service.init_arduino(ip_arduino)' in line:
            new_lines.append(line.replace('ip_arduino', 'mqtt_service.ip_arduino'))
            continue
        # Stop skipping here
        if line.startswith('def fonction_led_programmer'):
            skip = False
        else:
            continue
        
    new_lines.append(line)

text = "".join(new_lines)

# Now we need to replace all instances of the variables in the file
replacements = {
    "mode_arrosage_dict": "mqtt_service.mode_arrosage_dict",
    "etat_pompe_dict": "mqtt_service.etat_pompe_dict",
    "tentatives_arrosage": "mqtt_service.tentatives_arrosage",
    "timers_actifs": "mqtt_service.timers_actifs",
    "derniere_donnees_capteurs_conf": "mqtt_service.derniere_donnees_capteurs_conf",
}

for k, v in replacements.items():
    # Only replace if not already replaced
    text = re.sub(r'(?<!\.)\b' + k + r'\b', v, text)

# Function call replacements
# fonction_led_programmer
text = text.replace("led_on_thread(ip_arduino, id_parc)", "ledService.led_on_thread(ip_arduino, id_parc, mqtt_service.etat_pompe_dict, mqtt_service.mqtt_client, mqtt_service.topic_etat_pompe)")
# The args for led_off_thread in timer
text = text.replace("args=[ip_arduino, list_id_parc])", "args=[ip_arduino, list_id_parc, mqtt_service.etat_pompe_dict, mqtt_service.mqtt_client, mqtt_service.topic_etat_pompe])")
text = text.replace("led_off_thread", "ledService.led_off_thread")

# In planifier:
text = text.replace("planificationService.planifier(id_parc, ip_arduino,", "planificationService.planifier(id_parc, mqtt_service.ip_arduino,")

# In led_off:
text = text.replace("status_code = ledService.led_off_thread(ip_arduino, list_id_parc)", "status_code = ledService.led_off_thread(mqtt_service.ip_arduino, list_id_parc, mqtt_service.etat_pompe_dict, mqtt_service.mqtt_client, mqtt_service.topic_etat_pompe)")

# In led_on: 
# led_on_thread(ip_arduino, id_parc) becomes:
text = text.replace("ledService.led_on_thread(ip_arduino, id_parc)", "ledService.led_on_thread(mqtt_service.ip_arduino, id_parc, mqtt_service.etat_pompe_dict, mqtt_service.mqtt_client, mqtt_service.topic_etat_pompe)")
# The timer in led_on:
text = text.replace("args=[ip_arduino, list_id_parc])", "args=[mqtt_service.ip_arduino, list_id_parc, mqtt_service.etat_pompe_dict, mqtt_service.mqtt_client, mqtt_service.topic_etat_pompe])")

# Save
with open(filepath, "w", encoding="utf-8") as f:
    f.write(text)

