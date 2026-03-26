import streamlit as st
from streamlit_option_menu import option_menu
import streamlit.components.v1 as components
import requests
from datetime import datetime, time
import time as t_sleep
import jwt
import json
from streamlit_cookies_manager import EncryptedCookieManager

st.set_page_config(page_title="Dashboard IoT", layout="wide")

key = "my_secret_key_pfe_gestion_participative_parcelle_urbaine_2026"

cookies = EncryptedCookieManager(
    password=key
)

if not cookies.ready():
    st.info("Erreur de chargement des cookies")
    st.stop()   

#Pour vérifier si la variable de session n'a encore été défini ainsi que la cookie
# est vide alors on définit la variable de session à None
if 'parcelle_data' not in st.session_state:
    st.session_state.parcelle_data = None
    
# Si la variable de session qui est censé contenir la liste des parcelles réserver par
# l'utilisateur qui est connecté au plateforme (donnée json) est vide alors on décode
# le token JWT pour récupérer les données de la session PHP dans le cas contraire on ignore
# le décodage du token JWT. Pour éviter de décoder un token Jwt expiré ou invalide
token = st.query_params.get("token")
if token:
    try:
        decoded_token = jwt.decode(token, key, algorithms=["HS256"])
        st.session_state.parcelle_data = decoded_token['parcelles']

        #On stocke les données de la session PHP dans la cookie pour conserver les données
        cookies['saved_data'] = json.dumps(st.session_state.parcelle_data)
        #On enregistre la cookie créée dans le navigateur
        cookies.save()
        st.query_params.clear()        
    except (jwt.ExpiredSignatureError, jwt.InvalidTokenError):
        st.warning("Veuillez passer par la plateforme PHP pour accéder au Tableau de bord")
        
elif st.session_state.parcelle_data is None and 'saved_data' in cookies:
    # Si la cookie est présente on charge les données qui y sont stockées pour 
    # l'affecter à la variable de session. Utile pour conserver les données après
    # une réactualisation de la page par exemple.
    st.session_state.parcelle_data = json.loads(cookies['saved_data'])        

parcelle_data = st.session_state.parcelle_data

# st.sidebar -> pour créer l'onglet vertical des Menu sur le côté de mon interface
with st.sidebar: 
    selected = option_menu( 
        menu_title="MENU", 
        options=["Tableau de bord", "Alerte & recommandation"], 
        orientation="vertical", 
        styles={ 
            "container": {"padding": "0!important", "background-color": "#f0f2f6"}, 
            "nav-link": { "font-size": "16px", "text-align": "left", "margin": "0px", "--hover-color": "#eee", }, 
            "nav-link-selected": { "background-color": "#4CAF50", # couleur encadré 
            "color": "white", # texte blanc 
            "font-weight": "bold", "border-radius": "8px", }, } ) # Affichage selon la sélection 

    list_id_parc = [p["Id_parc"] for p in parcelle_data]
    choix_parcelle = st.selectbox("choix parcelle", list_id_parc)

if selected == "Tableau de bord":
    #------------------------ CONTROLE DES LED --------------------------------------#
    ############ DEBUT 

    st.title("MONITORING EN TEMPS REEL")        

    #------------------------ RECUPERATION DONNE CAPTEUR --------------------------------------#
    # -------------------------------------------------------------
    # COMPOSANT TEMPS RÉEL (MQTT OVER WEBSOCKETS)
    # -------------------------------------------------------------
    # Ce bloc est en HTML/JS pur. Il reçoit les données et met à jour
    # le texte SANS JAMAIS recharger la page Streamlit.
    # -------------------------------------------------------------
    ############ DEBUT 
    mqtt_recuperate_data_capteur = f"""
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .data-container {{
                display: flex;
                justify-content: center;
                align-items: center;
                font-family: 'Inter', sans-serif;
                margin-top: 40px; /* Plus de décalage avec le haut de la page */
                flex-wrap: wrap;
                gap: 40px; /* Plus d'espace (décalage) entre les boîtes */
            }}

            .data-box {{
                background: #ffffff;
                border: 1px solid #f1f5f9; /* Bordure ultra discrète */
                border-radius: 20px; /* Arrondis très doux */
                padding: 30px; /* Énormément d'espace à l'intérieur pour respirer */
                width: 220px; /* Boîtes élargies */
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.02); /* Ombre presque invisible, juste pour le volume */
                display: flex;
                flex-direction: column;
                align-items: flex-start; /* Alignement à gauche (très professionnel) */
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }}

            /* Animation au survol tout en douceur */
            .data-box:hover {{
                transform: translateY(-5px);
                box-shadow: 0 15px 45px rgba(0, 0, 0, 0.06); 
            }}

            /* Icône dans un carré arrondi (Squircle : style iOS) */
            .icon-wrapper {{
                width: 48px;
                height: 48px;
                border-radius: 14px; 
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 30px; /* Décalage très prononcé entre l'icône et les textes */
                font-size: 20px;
            }}
            
            /* Couleurs pastel très raffinées pour les icônes */
            .hum-box .icon-wrapper {{ background: #eff6ff; color: #3b82f6; }}
            .temp-box .icon-wrapper {{ background: #fef2f2; color: #ef4444; }}
            .lum-box .icon-wrapper {{ background: #fffbeb; color: #f59e0b; }}

            /* Le titre du capteur (petit et adouci) */
            .data-box h5 {{
                margin: 0;
                font-size: 14px;
                color: #94a3b8; /* Gris clair élégant */
                font-weight: 500; /* Police ni trop fine, ni trop grasse */
            }}

            /* La valeur dynamique (Grosse et impactante) */
            .data-box span {{
                display: block;
                margin-top: 8px; /* Léger décalage avec le petit titre */
                font-size: 32px; /* Valeur plus grosse */
                font-weight: 700;
                color: #0f172a; /* Gris très sombre, quasiment noir */
                letter-spacing: -0.5px; /* Rapproche légèrement les chiffres pour un look moderne */
            }}
        </style>

        <!-- L'affichage des différentes boites -->
        <div class="data-container"> 
            <!-- Boite Humidité -->
            <div class="data-box hum-box">
                <div class="icon-wrapper"><i class="fa-solid fa-droplet"></i></div>
                <h5>Humidité</h5>
                <span id="value_data_humidite">0 %</span>
            </div>
            
            <!-- Boite Température -->
            <div class="data-box temp-box">
                <div class="icon-wrapper"><i class="fa-solid fa-temperature-half"></i></div>
                <h5>Température</h5>
                <span id="value_data_temperature">0.0 °C</span>
            </div>

            <!-- Boite Luminosité -->
            <div class="data-box lum-box">
                <div class="icon-wrapper"><i class="fa-solid fa-sun"></i></div>
                <h5>Luminosité</h5>
                <span id="value_data_luminosite">0.0 lx</span>
            </div>
        </div>


        <script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/5.14.1/mqtt.min.js"></script>
        <script>
            // 1. Connexion au Broker MQTT via WebSocket comme vous le faisiez
            const broker = 'ws://192.168.100.117:9001'; 

            const topic_temperature = 'data/temperature/{choix_parcelle}';
            const topic_luminosite = 'data/luminosite/{choix_parcelle}';
            const topic_humidite = 'data/humidite/{choix_parcelle}';

            const client_mqtt = mqtt.connect(broker);
                        
            const valueElement_temperature = document.getElementById('value_data_temperature');
            const valueElement_humidite = document.getElementById('value_data_humidite');
            const valueElement_luminosite = document.getElementById('value_data_luminosite');
            
            client_mqtt.on('connect', () => {{
                // Abonnement aux topics de la parcelle sélectionnée
                client_mqtt.subscribe(topic_temperature);
                client_mqtt.subscribe(topic_luminosite);
                client_mqtt.subscribe(topic_humidite);
                console.log("Streamlit Display: Connecté aux topics en temps réel.");
            }});
            
            // 2. Traitement uniquement pour L'AFFICHAGE !
            client_mqtt.on('message', (topic, message) => {{
                
                // Température
                if (topic === topic_temperature){{
                    const val_dht11 = message.toString();
                    const data_dht11 = JSON.parse(val_dht11);
                    
                    if(data_dht11.temperature === "error" || data_dht11.temperature === -999) {{
                        valueElement_temperature.innerText = "Erreur";
                    }} else {{
                        valueElement_temperature.innerText = data_dht11.temperature + " °C";
                    }}
                }}

                // Luminosité
                else if (topic === topic_luminosite){{
                    const val_bh1750 = message.toString();
                    const data_bh1750 = JSON.parse(val_bh1750);
                    
                    if(data_bh1750.luminosite === "error" || data_bh1750.luminosite === -999){{
                        valueElement_luminosite.innerText = "Erreur";
                    }} else {{
                        valueElement_luminosite.innerText = data_bh1750.luminosite.toFixed(2) + " lx";
                    }}
                }}

                // Humidité
                else if (topic === topic_humidite){{
                    const val_raindrop_sensor = message.toString();
                    const data_raindrop_sensor = JSON.parse(val_raindrop_sensor);
                    
                    if (data_raindrop_sensor.humidite === "error" || data_raindrop_sensor.humidite === -999){{
                        valueElement_humidite.innerText = "Erreur";
                    }} else {{
                        valueElement_humidite.innerText = data_raindrop_sensor.humidite + " %";
                    }}
                }}
            }});
        </script>
    """
    
    # Intégration dans Streamlit
    components.html(mqtt_recuperate_data_capteur, height=250)

    # --- LE RESTE DE VOTRE INTERFACE (LED, ARROSAGE) ---
    st.divider()

    # --- LE PLUS IMPORTANT : La pause ---
    t_sleep.sleep(1)

    if 'mode_manuel' not in st.session_state :
        st.session_state.mode_manuel = False
    
    def changer_etat_boutton():
        st.session_state.mode_manuel = not st.session_state.mode_manuel
    
    label_button = "Mode : Auto" if not st.session_state.mode_manuel else "Mode : Manuel"

    with st.container(border=True):
        st.button(label_button, on_click=changer_etat_boutton, type="secondary")

        if not st.session_state.mode_manuel:
            payload = {
                "mode": "auto"
            }

            response_activation_auto = requests.post(f"http://localhost:5000/mode/arrosage/id_parc={choix_parcelle}", json=payload)

        elif st.session_state.mode_manuel:
            payload={
                "mode": "manuel"
            }

            response_activation_manuel = requests.post(f"http://localhost:5000/mode/arrosage/id_parc={choix_parcelle}", json=payload)
            
            col1, col2 = st.columns(2)

            with col1:
                if st.button('Activer Pompe'):
                    try:
                        response = requests.post(f"http://localhost:5000/led/on/id_parc={choix_parcelle}")
                        data = response.json()
                        
                        if(data['status'] == "success"):
                            st.success(f"{choix_parcelle} allume -> {data['message']} - {data['status']}")
                        else:
                            st.error("Erreur lors de l'activation de la pompe")

                    except Exception as e:
                        st.error("Serveur Flask injoignable")

            with col2:
                if st.button('Eteindre Pompe'):
                    try:
                        response = requests.post(f"http://localhost:5000/led/off/id_parc={choix_parcelle}")
                        data = response.json()

                        if(data['status'] == "success"):
                            st.success(f"{choix_parcelle} eteint -> {data['message']} - {data['status']}")
                        else:
                            st.error("Erreur lors de l'extinction de la pompe")

                    except Exception:
                        st.error("Serveur Flask injoignable")
    ############ FIN

    col3, col4 = st.columns(2)
    
    with col3:
        with st.container(border=True):
            st.markdown(""" <h1 style="font-size: 25px; font-weight: 15px"> 
                                Statut Arrosage 
                            </h1>""", unsafe_allow_html=True)

            with st.container(border=True):
                for id_parc in list_id_parc:

                    # On demande à Flask l'état de départ de la pompe
                    try:
                        response = requests.get(f"http://localhost:5000/status/pompe/id_parc={id_parc}")

                        etat_arrosage = response.json()['etat_pompe_dict']

                        # On définit la valeur de départ pour l'affichage du statut de la pompe
                        couleur_fond = "#d0f0c0" if etat_arrosage == "Active" else "#f8d7da"
                        couleur_bord = "green" if etat_arrosage == "Active" else "red"
                        texte = "Active" if etat_arrosage == "Active" else "Desactive"

                    # On affecte une valeur par défaut dans le cas où la requête échoue
                    except:
                        etat_arrosage = "Desactive"
                    
                    # Affichage du statut de la pompe
                    affichage_etat_pompe = f"""
                        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&display=swap" rel="stylesheet">
                        <style>
                            body {{ margin: 0; font-family: 'Inter', sans-serif; }}
                            .boite {{ background-color: {couleur_fond}; border: 2px solid {couleur_bord}; border-radius: 8px; padding: 10px; transition: 0.3s; }}
                        </style>
                        
                        <!-- L'affichage visuel -->
                        <div id="ma_boite" class="boite">
                            Pompe -> {id_parc} : <span id="mon_texte">{texte}</span>
                        </div>
                        
                        <!-- Le mini-cerveau JavaScript (connecté au WebSocket) -->
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/5.14.1/mqtt.min.js"></script>
                        <script>
                            // 1. Connexion au broker
                            const client = mqtt.connect("ws://192.168.100.117:9001");

                            // 2. Capture de l'élément HTML pour l'affichage dynamique du statut d'arrosage de la pompe
                            const valueElement_status_pompe = document.getElementById("mon_texte");
                            const valueElement_boite = document.getElementById("ma_boite");
                            
                            // 3. Abonnement au topic
                            const topic = "status/pompe/{id_parc}";

                            // 4. Fonction appelée lorsque le client MQTT se connecte au broker MQTT
                            client.on("connect", () => {{
                                client.subscribe(topic);
                                console.log("Connecté au broker MQTT sur le topic : " + topic);
                            }});
                            
                            // 5. Fonction appelée à chaque message reçu du broker MQTT
                            client.on("message", (topic, payload) => {{
                                const msg_recu_status_pompe = payload.toString();

                                if(msg_recu_status_pompe == "Active"){{
                                    valueElement_status_pompe.textContent = "Active";
                                    valueElement_boite.style.backgroundColor = "#d0f0c0";
                                    valueElement_boite.style.borderColor = "green";
                                }} else {{
                                    valueElement_status_pompe.textContent = "Desactive";
                                    valueElement_boite.style.backgroundColor = "#f8d7da";
                                    valueElement_boite.style.borderColor = "red";
                                }}
                            }});

                        </script>
                    """

                    # On affiche la petite boîte pour statut arrosage 
                    components.html(affichage_etat_pompe, height=50)

    with col4:
        #------------------------ PROGRAMMATION ARROSAGE --------------------------------------#
        ############ DEBUT 
        with st.form(key="programmation_arrosage"):
            st.header("🕒 Paramètres de la prochaine session d'arrosage")

            date_choisie = st.date_input("Choisir la date d'arrosage", value=datetime.today(), min_value=datetime.today())

            c1, c2, c3 = st.columns([2, 1, 2])

            with c1:
                heure = st.number_input("Heure", min_value=0, max_value=23, value=0, step=1)

            with c2:
                st.markdown("<h1  style='text-align: center; padding-top: 15px;'>:</h1>", unsafe_allow_html=True)

            with c3:
                minute = st.number_input("Minute", min_value=0, max_value=59, value=0, step=1)
            
            heure_formate = time(heure, minute)
            moment_programme = datetime.combine(date_choisie, heure_formate)

            duree_arrosage = st.slider("Durée de l'arrosage", min_value=1, max_value=60, value=10, step=1)

            payload = {
                "date_heure": moment_programme.strftime("%Y-%m-%d %H:%M"),
                "duree": duree_arrosage
            }

            col1, col2 = st.columns(2)

            with col1:
                if st.form_submit_button("Programmer l'arrosage"):
                    try:
                        if moment_programme < datetime.now() or duree_arrosage == 0:
                            st.error("L'heure choisie est déjà passée ou la durée est de 0")
                        else:
                            response = requests.post(f"http://localhost:5000/planifier/id_parc={choix_parcelle}", json=payload)
                            data = response.json()
                            st.success(f"{data['message']} pour {data['date_execution']} pour une durée de {data['duree']} minutes - {data['status']}")
                    except Exception:
                        st.error("Serveur Flask injoignable")
            
            with col2:
                if st.form_submit_button("Annuler l'arrosage"):
                    try:
                        response = requests.post(f"http://localhost:5000/cancelPlanifier/id_parc={choix_parcelle}")
                        data = response.json()

                        if data["status"] == 200:
                            st.success(data["message"])
                        else:
                            st.error(data["message"])
                    except Exception:
                        st.error("Serveur Flask injoignable")
        ############ FIN

