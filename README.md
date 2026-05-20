# UrbanCultive

UrbanCultive est une plateforme web et IoT dédiée à la gestion participative et intelligente de parcelles de culture urbaine. Elle permet aux citoyens de réserver des parcelles de terre partagées, de collaborer via un système d'avis et d'assurer un suivi en temps réel de leurs plantations grâce à un système d'arrosage connecté et automatisé.

---

## Fonctionnalités principales

* **Portail Web (PHP / MVC)**
  * **Espace Client :** Consultation du catalogue des parcelles disponibles, réservations en ligne, gestion des abonnements, et publication d'avis ou recommandations au sein de la communauté.
  * **Espace Administration :** Gestion complète des utilisateurs, approbation ou refus des demandes de réservation, modération des avis et édition du catalogue de parcelles.
  * **Sécurisation par jetons :** Authentification renforcée et partage sécurisé de données vers le tableau de bord IoT via des tokens JWT.
  * **Notifications automatisées :** Alertes par email (confirmations, avertissements de fin de bail, rappels automatiques).

* **Tableau de Bord de Monitoring (Python / Streamlit)**
  * **Suivi en temps réel :** Visualisation des données environnementales (température de l'air, luminosité ambiante, humidité du sol) grâce à un flux MQTT direct (WebSockets) intégré à l'interface.
  * **Pilotage manuel :** Commande d'activation ou d'arrêt forcé des pompes d'arrosage (matérialisées par des relais).
  * **Planification avancée :** Programmation de sessions d'irrigation à des dates et heures précises avec gestion de fréquence et de répétition (Flask-APScheduler).
  * **Régulation intelligente (Mode Auto) :** Activation autonome de l'irrigation en fonction de seuils personnalisables de température et d'humidité du sol.
  * **Notifications Telegram :** Envoi d'alertes instantanées sur smartphone concernant les changements de mode de fonctionnement ou les anomalies.

* **Système Physique (Arduino / C++)**
  * **Arduino Uno :** Lecture directe des capteurs (capteur d'humidité du sol, capteur de température/humidité DHT11, capteur de lumière BH1750).
  * **ESP32 :** Gestion de la connectivité réseau (WiFi), résolution mDNS et publication/abonnement MQTT pour l'échange bidirectionnel avec le backend.

---

## Architecture technique

L'application repose sur une architecture hybride combinant le développement web classique et les technologies de l'IoT :

* **Frontend Web :** PHP (architecture MVC), HTML, CSS, JavaScript.
* **Dashboard IoT :** Streamlit (Python).
* **Serveur d'API & Planificateur :** Flask, Flask-APScheduler, SQLAlchemy (Python).
* **Communication IoT :** MQTT (via un broker Mosquitto), WebSockets pour le temps réel dans le navigateur.
* **Base de données :** MySQL.
* **Matériel :** Microcontrôleurs Arduino Uno & ESP32, capteurs DHT11, BH1750, sonde d'humidité du sol, module relais et pompe à eau.

---

## Installation et configuration

### Prérequis

* Un serveur web local avec PHP 8.x et MySQL (par exemple XAMPP, WampServer ou Laragon).
* Python 3.x installé sur votre machine.
* Un broker MQTT fonctionnel (comme Mosquitto) accessible sur votre réseau local.
* L'IDE Arduino pour le téléversement des codes sur les microcontrôleurs.

### 1. Base de données

1. Démarrez votre serveur MySQL.
2. Créez une base de données nommée `gestion_participatif`.
3. Importez le fichier SQL de structure situé à l'emplacement suivant :
   `repository/data_source/BD/schema_bd.sql`

### 2. Partie Web (PHP)

1. Placez le dossier du projet dans le répertoire racine de votre serveur web (par exemple `C:\xampp\htdocs\Document_PFE`).
2. Assurez-vous que le fichier `config/MySQL.php` contient les bonnes informations de connexion à votre base de données MySQL.
3. Installez les dépendances Composer requises pour la gestion des jetons JWT en exécutant la commande suivante dans le terminal :
   ```bash
   composer install
   ```
4. Accédez à la plateforme via votre navigateur à l'adresse suivante :
   `http://localhost/Document_PFE/views/introduction/PresentationSite.php`

### 3. Services Backend et Tableau de Bord (Python)

1. Ouvrez un terminal dans le répertoire racine du projet.
2. Créez un environnement virtuel Python et installez les dépendances listées dans `requirements.txt` :
   ```bash
   python -m venv venv
   source venv/bin/activate  # Sur Windows : venv\Scripts\activate
   pip install -r requirements.txt
   ```
3. Vérifiez la configuration de la base de données dans `config/db_python/db_config.py`.
4. Renseignez l'adresse IP de votre broker MQTT ainsi que celle de votre carte ESP32 dans le constructeur de la classe `RecuperateDataSensorMQTT` (dans `services/client/donnee_capteur_service/recuperate_data_sensor_mqtt.py`).
5. Lancez le serveur Flask (API et gestion de la planification) :
   ```bash
   python controllers/client/tableau_de_bord.py
   ```
6. Dans un autre terminal (avec l'environnement virtuel activé), démarrez l'interface Streamlit :
   ```bash
   streamlit run views/client/tableau_de_bord/Controle.py
   ```

### 4. Partie Matériel (Arduino)

1. Connectez votre Arduino Uno et votre ESP32 à votre ordinateur.
2. Ouvrez le croquis ESP32 situé dans `Arduino/sketch_arduino_esp32/` avec l'IDE Arduino.
3. Ajustez les configurations réseau et MQTT dans les fichiers d'en-tête :
   * `Config_WiFi.h` : Saisissez le SSID et le mot de passe de votre réseau WiFi local.
   * `Config_MQTT.h` : Renseignez l'adresse IP et le port de votre broker MQTT.
4. Téléversez le code sur l'ESP32.
5. Ouvrez le croquis Uno situé dans `Arduino/sketch_arduino_uno/` et téléversez-le sur la carte Arduino Uno.
