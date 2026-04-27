import streamlit as st
from streamlit_option_menu import option_menu
import streamlit.components.v1 as components
import requests
from datetime import datetime, time
import time as t_sleep
import jwt
import json
from streamlit_cookies_manager import EncryptedCookieManager
import pandas as pd
import plotly.graph_objects as go

@st.fragment
def bouton_commande_pompe(choix_parcelle, duree_arrosage):

    col3, col4 = st.columns(2)

    with col3:
        if st.button('Activer Pompe'):
            try:
                payload_duree_arrosage = {
                    "duree_arrosage_manuelle": duree_arrosage,
                }

                response = requests.post(f"http://localhost:5000/led/on/id_parc={choix_parcelle}", json=payload_duree_arrosage)
                data = response.json()
                
                if(data['status_code'] == 200):
                    st.toast(f"{choix_parcelle} allume -> {data['message']} - {data['status']}", icon="✅")
                else:
                    st.toast(data['message'], icon="🛑")

            except Exception as e:
                st.toast(f"Serveur Flask injoignable pour l'activation de la pompe -> {choix_parcelle}", icon="⚠️")

    with col4:
        if st.button('Eteindre Pompe'):
            try:
                payload = {
                    "list_id_parc": [f"{choix_parcelle}"]
                }
                
                response = requests.post(f"http://localhost:5000/led/off", json=payload)
                data_json = response.json()

                if data_json['status_code'] == 200:
                    st.toast(f"{choix_parcelle} eteint -> {data_json['message']} - {data_json['status']}", icon="✅")
                else:
                    st.toast("Erreur lors de l'extinction de la pompe", icon="🛑")
                    
            # On précise l'erreur pour ne pas attraper le st.rerun()
            except Exception:
                st.toast("Serveur Flask injoignable pour désactivation de la pompe", icon="⚠️")
    
@st.fragment
def bouton_create_seuil():
    if st.button("Créer seuil"):
        st.session_state.bouton_create_clique = True
        st.rerun(scope="app")

@st.fragment
def suppr_data_planification(id_planning, choix_parcelle):
    payload_delete_planification = {
        "id_planning": id_planning
    } 

    try:
        response = requests.delete(f"http://localhost:5000/cancelPlanifier/id_parc={choix_parcelle}", json=payload_delete_planification)
        data_json = response.json()
        status_code = data_json.get("status_code")

        if status_code == 200:
            st.toast("Suppression planification réussi", icon="✅")
            st.rerun(scope="app")
        else:
            st.toast("Échec de la suppression", icon="🛑")
    
    except requests.exceptions.RequestException:
        st.toast("Serveur Flask injoignable", icon="⚠️")

def changer_status_planification(id_planning, status_actuel, choix_parcelle):
    payload_toggle = {
        "id_parc": choix_parcelle,
        "id_planning": id_planning,
        "status": status_actuel # Statut actuel (Actif/Inactif)
    }

    try:
        response = requests.put(f"http://localhost:5000/update_status_planification_activation", json=payload_toggle)

        data_json = response.json()

        if data_json.get("status_code") == 200:
            st.toast(f"Planification activé pour id_planning = {id_planning}", icon="✅")
            st.rerun()
        
        else:
            st.toast(f"Erreur serveur ({data_json.get("status_code")})", icon="🛑")
    
    except Exception as e:
        st.toast(f"Serveur Injoignable pour mise à jour status planification pour id_planning = {id_planning} -> {e}", icon="⚠️")

st.set_page_config(page_title="Dashboard IoT", layout="wide")

key = "my_secret_key_pfe_gestion_participative_parcelle_urbaine_2026"

cookies = EncryptedCookieManager(
    password=key
)

if not cookies.ready():
    st.info("Initialisation du tableau de bord...")
    st.stop()   

#Pour vérifier si la variable de session n'a encore été défini ainsi que la cookie
# est vide alors on définit la variable de session à None
if 'parcelle_data' not in st.session_state:
    st.session_state.parcelle_data = None

if "user_id" not in st.session_state:
    st.session_state.user_id = None

# Si la variable de session qui est censé contenir la liste des parcelles réserver par
# l'utilisateur qui est connecté au plateforme (donnée json) est vide alors on décode
# le token JWT pour récupérer les données de la session PHP dans le cas contraire on ignore
# le décodage du token JWT. Pour éviter de décoder un token Jwt expiré ou invalide
token = st.query_params.get("token")
if token:
    try:
        decoded_token = jwt.decode(token, key, algorithms=["HS256"])

        st.session_state.parcelle_data = decoded_token['parcelles']
        st.session_state.user_id = decoded_token['user_id_user']

        #On stocke les données de la session PHP dans la cookie pour conserver les données
        cookies['saved_data_project'] = json.dumps({
            "parcelle_data": st.session_state.parcelle_data,
            "user_id": st.session_state.user_id
        })

        #On enregistre la cookie créée dans le navigateur
        cookies.save()

        st.query_params.clear()        
    except (jwt.ExpiredSignatureError, jwt.InvalidTokenError):
        st.toast("Veuillez passer par la plateforme PHP pour accéder au Tableau de bord", icon="⚠️")
        
elif st.session_state.parcelle_data is None and 'saved_data_project' in cookies:
    # Si la cookie est présente on charge les données qui y sont stockées pour 
    # l'affecter à la variable "saved_data_project". Utile pour conserver les données après
    # une réactualisation de la page par exemple.
    saved_data_project = json.loads(cookies['saved_data_project'])        

    st.session_state.parcelle_data = saved_data_project.get("parcelle_data")
    st.session_state.user_id = saved_data_project.get("user_id")
    print(st.session_state.parcelle_data)

parcelle_data = st.session_state.parcelle_data
user_id = st.session_state.user_id

# st.sidebar -> pour créer l'onglet vertical des Menu sur le côté de mon interface
with st.sidebar: 
    selected = option_menu( 
        menu_title="MENU", 
        options=["Tableau de bord", "Analyse des données"], 
        orientation="vertical", 
        styles={ 
            "container": {"padding": "0!important", "background-color": "#f0f2f6"}, 
            "nav-link": { "font-size": "16px", "text-align": "left", "margin": "0px", "--hover-color": "#eee", }, 
            "nav-link-selected": { "background-color": "#4CAF50", # couleur encadré 
            "color": "white", # texte blanc 
            "font-weight": "bold", "border-radius": "8px", }, } ) # Affichage selon la sélection 

    list_id_parc = [p["Id_parc"] for p in parcelle_data]
    choix_parcelle = st.selectbox("choix parcelle", list_id_parc)

# ---------------------------------------- INTERFACE DU TABLEAU DE BORD STREAMLIT ------------------------------------------------
if selected == "Tableau de bord":
    # On définit une valeur par défaut au départ pour éviter que l'interface plante à cause d'une éventuelle erreur qui pourrais se passer
    # dans le bloc try...except 
    mode_systeme_actuel = "Auto"

    try:
        # Tentative de récupération depuis l'API
        response = requests.get(f"http://localhost:5000/select_mode_systeme/id_parc={choix_parcelle}")   
        data_json = response.json()
        status_code = data_json.get("status_code")

        if status_code == 200:
            mode_systeme_actuel = data_json.get("mode_systeme_actuel")

        elif status_code == 400:
            # Si la récupération du mode système échoué à cause d'une erreur au niveau de la base de donnée
            # alors nous allons retourner la valeur par défaut "Mode : Auto" pour éviter que le système plante
            message = data_json.get("message")
            st.toast(message, icon="⚠️")
    
    except Exception :
        st.toast("Serveur injoignable : Utilisation du mode par défaut (Auto).", icon="⚠️")
    #------------------------ CONTROLE DES LED --------------------------------------#
    ############ DEBUT 

    st.markdown("""
        <h1 style='text-align: left; margin-top: -95px; margin-bottom: 30px; font-size: 40px; font-weight: 700;'>
            MONITORING EN TEMPS REEL
        </h1>
    """, unsafe_allow_html=True)        

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
                margin-top: 30px;
                flex-wrap: nowrap;
                gap: 80px;
            }}

            .data-box {{
                background: #ffffff;
                border: 1px solid #edf2f7;
                border-radius: 16px;
                padding: 22px 18px;
                width: 190px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }}

            .data-box:hover {{
                transform: translateY(-4px);
                box-shadow: 0 12px 20px rgba(0, 0, 0, 0.06);
                border-color: #e2e8f0;
            }}

            .icon-wrapper {{
                width: 44px;
                height: 44px;
                border-radius: 12px;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-bottom: 15px;
                font-size: 18px;
                transition: transform 0.3s ease;
            }}

            .data-box:hover .icon-wrapper {{
                transform: scale(1.1);
            }}
            
            .hum-box .icon-wrapper {{ background: #f0f9ff; color: #0ea5e9; }}
            .temp-box .icon-wrapper {{ background: #fff1f2; color: #f43f5e; }}
            .lum-box .icon-wrapper {{ background: #fffbeb; color: #f59e0b; }}

            .data-box h5 {{
                margin: 0;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                font-weight: 600;
            }}

            .data-box span {{
                display: block;
                margin-top: 6px;
                font-size: 26px;
                font-weight: 700;
                color: #1e293b;
                letter-spacing: -0.02em;
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
            // 1. Connexion au Broker MQTT via WebSocket
            const broker =  'ws://10.119.163.221:9001';

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
                    
                    if(data_dht11.temperature === "error" || data_dht11.temperature === -999 || data_dht11.temperature === "undefined") {{
                        valueElement_temperature.innerText = "Erreur";
                    }} else {{
                        valueElement_temperature.innerText = data_dht11.temperature + " °C";
                    }}
                }}

                // Luminosité
                else if (topic === topic_luminosite){{
                    const val_bh1750 = message.toString();
                    const data_bh1750 = JSON.parse(val_bh1750);
                    
                    if(data_bh1750.luminosite === "error" || data_bh1750.luminosite === -999 || data_bh1750.luminosite.luminosite === "undefined"){{
                        valueElement_luminosite.innerText = "Erreur";
                    }} else {{
                        valueElement_luminosite.innerText = data_bh1750.luminosite.toFixed(2) + " lx";
                    }}
                }}

                // Humidité
                else if (topic === topic_humidite){{
                    const val_raindrop_sensor = message.toString();
                    const data_raindrop_sensor = JSON.parse(val_raindrop_sensor);
                    
                    if (data_raindrop_sensor.humidite === "error" || data_raindrop_sensor.humidite === -999 || data_raindrop_sensor.humidite === "undefined"){{
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

    col1, col2 = st.columns(2)

    with col1:
        with st.container(border=True):
            st.markdown(""" <h1 style="font-size: 25px; font-weight: 15px"> 
                                Actions rapide 
                            </h1>""", unsafe_allow_html=True)

            if st.button("Forcer extinctions des pompes"):
                try:
                    payload = {
                        "list_id_parc": list_id_parc
                    }

                    response = requests.post(f"http://localhost:5000/led/off", json=payload)
                    data = response.json()

                    if data["status_code"] == 200:
                        st.toast(f"Extinction des pompes {list_id_parc} réussi.", icon="✅")
                    else:
                        st.toast("Erreur survenu lors de la tentative d'extinction des pompes.", icon="🛑")

                except Exception as e:
                    st.toast("Serveur Flask injoignable.", icon="⚠️")
    
    with col2:
        with st.container(border=True):
            st.markdown(""" <h1 style="font-size: 25px; font-weight: 15px"> 
                                Status Arrosage 
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

                        couleur_fond = "#f8d7da"
                        couleur_bord = "red"
                        texte = "Serveur Flask injoignable"
                    
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
                            const client = mqtt.connect("ws://10.119.163.221:9001"); 

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

    with st.container(border=True):
        if st.button(f"Mode : {mode_systeme_actuel}"):
            try:
                payload_mode_systeme = {
                    "mode_systeme_actuel": mode_systeme_actuel,
                    "id_parc": choix_parcelle
                }

                response = requests.put(f"http://localhost:5000/update_mode_systeme", json=payload_mode_systeme)
                data_json = response.json()

                status_code = data_json.get("status_code")
                mode_systeme_update = data_json.get("mode_systeme_update")

                if status_code == 200:
                    st.toast(f"Mode \"{mode_systeme_update}\" activé !", icon="✅")
                    st.rerun()

                elif response.status_code == 400:
                    st.toast(f"Echec de changement du mode système !", icon="🛑")
            
            except Exception:
                st.toast("Serveur Flask injoignable pour la récupération du mode du système !", icon="⚠️")

        if mode_systeme_actuel == "Auto":
            try:                
                response_recup_seuil_data = requests.get(f"http://localhost:5000/recuperate/seuillage/id_parc={choix_parcelle}")
                data_json = response_recup_seuil_data.json()
                liste_seuil_data = data_json.get("liste_seuil_data")

                if liste_seuil_data:
                    for p in liste_seuil_data:
                        id_seuil, id_parc, temp_seuil, humidite_seuil = p

                        with st.form(key="update_seuil"):
                            col_seuil_1, col_seuil_2 = st.columns([2, 2])

                            if "edit_mode" not in st.session_state :
                                st.session_state.edit_mode = False

                            with col_seuil_1:
                                temp_seuil = st.number_input("Seuil Temperature (°C)", min_value=0, max_value=50, value=int(temp_seuil), step=1, disabled = not st.session_state.edit_mode)
                            
                            with col_seuil_2:
                                humidite_seuil = st.number_input("Seuil Humidite (%)", min_value=0, max_value=400, value=int(humidite_seuil), step=1, disabled = not st.session_state.edit_mode)

                            col_valider_seuil, col_modifier_seuil = st.columns(2)

                            if "etat_bouton_modif_clique" not in st.session_state:
                                st.session_state.etat_bouton_modif_clique = False

                            with col_valider_seuil:
                                if st.session_state.etat_bouton_modif_clique :
                                    if st.form_submit_button("Valider"):
                                        try:
                                            payload_register_seuil = {
                                                "id_parc": choix_parcelle,
                                                "temp_seuil": temp_seuil,
                                                "humidite_seuil": humidite_seuil
                                            }

                                            response = requests.post(f"http://localhost:5000/register/seuillage", json=payload_register_seuil)
                                            data_json = response.json()

                                            if data_json['status_code'] == 200:
                                                st.toast(f"Mise à jour réussi des valeurs de seuillage", icon="✅")
                                                t_sleep.sleep(1)
                                                
                                                # Une fois que l'utilisateur à valider la valeur des seuil (Temperature - Humidite). Les champs de saisie seront grisés
                                                st.session_state.edit_mode = False

                                                # Une fois que l'utilisateur a cliqué sur le bouton "Valider". On réapparaît le bouton "Modifier" à l'interface
                                                st.session_state.etat_bouton_modif_clique = False
                                                st.rerun()

                                            else :
                                                st.toast("Echec du mise à jour des valeurs de seuillage", icon="🛑")

                                        except Exception as e:
                                            st.toast("Serveur Flask injoignable valeur seuillage non mise à jour", icon="⚠️")                                            
                                        
                            with col_modifier_seuil:
                                if not st.session_state.etat_bouton_modif_clique:
                                    if st.form_submit_button('Modifier'):
                                        # Les champs devient saisible par l'utilisateur pour la modification des valeurs de seuil (Temperature - Humidite)
                                        st.session_state.edit_mode = True

                                        # On cache le bouton une fois qu'elle a été cliqué par l'utilisateur
                                        st.session_state.etat_bouton_modif_clique = True
                                        st.rerun()

                else:
                    # -------------- POUR FAIRE DISPARAITRE LE BOUTON "Créer seuil" APRES AVOIR CLIQUE SUR 
                    if "bouton_create_clique" not in st.session_state:
                        st.session_state.bouton_create_clique =  False
                    
                    if not st.session_state.bouton_create_clique:
                        bouton_create_seuil()    
                    # -------------- POUR FAIRE DISPARAITRE LE BOUTON "Créer seuil" APRES AVOIR CLIQUE SUR

                    else:
                        with st.form(key="create_new_seuil"):
                            col_new_seuil_1, col_new_seuil_2 = st.columns(2)

                            with col_new_seuil_1:
                                temp_seuil = st.number_input("Seuil Temperature (°C)", min_value=0, max_value=50, value=0, step=1)

                            with col_new_seuil_2:
                                humidite_seuil = st.number_input("Seuil Humidite (%)", min_value=0, max_value=400, value=0, step=1)
                            
                            payload_create_new_seuil = {
                                "id_parc": choix_parcelle,
                                "temp_seuil": temp_seuil,
                                "humidite_seuil": humidite_seuil
                            }

                            if st.form_submit_button("Enregistrer"):
                                response_create_new_seuil = requests.post("http://localhost:5000/create/seuillage", json=payload_create_new_seuil)

                                data_json = response_create_new_seuil.json()

                                if data_json["status_code"] == 200:
                                    st.toast("Valeur de seuillage enregistré", icon="✅")
                                    t_sleep.sleep(1) # Pour laisser le temps au notification de s'afficher avant le relancement de la lecture de tous le script depuis le début
                                    st.rerun()
                                else:
                                    st.toast("Valeur de seuillage non enregistré", icon="🛑")
                                                
            except Exception:
                st.toast("Système inactif : \"Serveur Flask injoignable\"", icon="⚠️")
                                    
        elif mode_systeme_actuel == "Manuel":
            duree_arrosage_manuelle = st.number_input("Duree arrosage (secondes)", min_value=0, max_value=60, value=0, step=1)

            bouton_commande_pompe(choix_parcelle, duree_arrosage_manuelle)
    ############ FIN

            #------------------------ PROGRAMMATION ARROSAGE --------------------------------------#
            ############ DEBUT 
            # Avec clear_on_submit = True -> pour spécifier qu'une fois que le formulaire soumis, tous les champs
            # sont réinitialisés (vidés)
            with st.container(border=True):

                st.header("🕒 Paramètres de la prochaine session d'arrosage")

                with st.form(key="programmation_arrosage", clear_on_submit=True):
                    date_choisie = st.date_input("Choisir la date d'arrosage", value=datetime.today(), min_value=datetime.today())

                    c1, c2, c3 = st.columns([2, 1, 2])

                    with c1:
                        heure = st.number_input("Heure", min_value=0, max_value=23, value=0, step=1)

                    with c2:
                        st.markdown("<h1 style='text-align: center; padding-top: 15px;'>:</h1>", unsafe_allow_html=True)

                    with c3:
                        minute = st.number_input("Minute", min_value=0, max_value=59, value=0, step=1)
                    
                    heure_formate = time(heure, minute)
                    moment_programme = datetime.combine(date_choisie, heure_formate)

                    duree_arrosage = st.number_input("Durée de l'arrosage (secondes)", min_value=1, max_value=60, value=1, step=1)

                    # Initialisation des variables par défaut (important si l'utilisateur dit 'Non')
                    intervalle_repetition = 0
                    nombre_repetition = 1

                    st.markdown("---") # Petite ligne de séparation visuelle
                    st.markdown("**Paramètres de simulation (Répétition) :**")
                    col1_config_freq, col2_config_freq = st.columns(2)

                    with col1_config_freq:
                        intervalle_repetition = st.number_input("Temps de pause entre arrosages (sec) :", min_value=5, value=5, step=1, help="Temps de pause entre chaque déclenchement")
                    
                    with col2_config_freq:
                        nombre_repetition = st.number_input("Nombres de répétitions :", min_value=1, value=1, step=1, help="Ex : Si valeur 3 -> Arrêter le processus de répétition après 3 activation de la pompe")

                    if st.form_submit_button("Programmer l'arrosage"):

                        payload_planification_arrosage = {
                            "date_heure": moment_programme.strftime("%Y-%m-%d %H:%M"),
                            "duree": duree_arrosage,
                            "intervalle_sec": intervalle_repetition,
                            "nombre_freq": nombre_repetition
                        }

                        try:
                            if moment_programme < datetime.now():
                                st.toast("L'heure choisie est déjà passée", icon="⚠️")

                            elif duree_arrosage <= 0:
                                st.toast("Duree arrosage doit être supérieur à 0", icon="⚠️")

                            else:
                                response = requests.post(f"http://localhost:5000/planifier/id_parc={choix_parcelle}", json=payload_planification_arrosage)
                                data = response.json()

                                if data["status_code"] == 200:
                                    st.toast(f"{data['message']} pour {data['date_execution']} pour une durée de {data['duree']} minutes - {data['status']}", icon="✅")
                                
                                elif data["status_code"] == 409:
                                    st.toast(f"L'Arrosage prévu existe déjà dans la base de donnée", icon="⚠️")

                                else :
                                    st.toast(data["message"], icon="🛑")

                        except Exception as e:
                            st.toast("Serveur Flask injoignable", icon="⚠️")
                ############ FIN
        
    with st.container(border=True):
        st.header("Liste Arrosage Planifié")

        if st.button("Rafraîchir la liste"):
            st.rerun()

        # response va contenir toutes les informations renvoyés par le serveur flask (réponse à la requête qui a été envoyé par l'utilisateur depuis l'interface streamlit)
        # Le Code de Statut : (200 pour succès, 404 si non trouvé, 500 si erreur serveur).
        # Les En-têtes (Headers) : Des méta-données (ex: Content-Type: application/json, la date, la taille du message).
        # Le Contenu Brut (Binary/Text) : Le message non encore interprété.
        # Le Temps de réponse : Combien de temps le serveur a mis pour répondre.
        response = requests.get(f"http://localhost:5000/historique_arrosage/id_parc={choix_parcelle}")

        # On demande à python ici d'ouvrir l'enveloppe de la lettre et de récupèrer les contenus du document json 
        data_json = response.json()

        liste_planification = data_json["list_data_planification"]

        # S'il existe belle et bien une liste des arrosages planifiées qui est stocké dans la variable "liste_planification".
        # Alors nous allons afficher le tableau dans la section "Liste Arrosage Planifié" sur mon interface streamlit
        if liste_planification:
            
            # Nous allons utiliser une boucle "for" pour extraire une à une toutes les tuples renvoyer par la requêtes précédente
            for p in liste_planification:
                id_planning, id_parc, duree_arrosage, date_arrosage_raw, heure_arrosage_raw, intervalle_rep, nombre_freq, status = p

                # On met chaque ligne de donnée dans un bloc de container (une sorte de "carte")
                with st.container():
                    col_info, col_button_action = st.columns([4, 1])

                    with col_info:
                        date_arrosage = str(date_arrosage_raw)
                        # On s'assure d'avoir un format HH:MM même si l'heure est à un seul chiffre (ex: 8:30:00 -> 08:30)
                        parts = str(heure_arrosage_raw).split(':')
                        heure_arrosage = f"{int(parts[0]):02d}:{int(parts[1]):02d}"

                        col_button_delete, col_heure_plan, col_button_toggle = st.columns([0.2, 1, 0.6])
                        
                        with col_button_delete:
                            # Espace vide pour descendre et centrer parfaitement la poubelle face à l'heure
                            st.markdown("<div style='margin-top: 15px;'></div>", unsafe_allow_html=True)
                            
                            # Simple bouton corbeille
                            # RÈGLE D'OR STREAMLIT : Il DOIT avoir une "key" unique liée à son ID pour ne pas crasher dans une boucle !
                            if st.button("🗑️", key=f"delete_btn_{id_planning}"):
                                suppr_data_planification(id_planning, choix_parcelle)
                    
                        with col_heure_plan:
                            # 2. Design "Historique Horloge / Timeline" ultra-moderne HTML/CSS
                            design_timeline = f"""
                            <div 
                                style="
                                    border-left: 4px solid #10b981; /* Barre verticale verte élégante */
                                    padding-left: 14px; 
                                    margin-bottom: 5px;"
                            >
                                <div style="font-size: 28px; font-weight: 800; font-family: 'Segoe UI', sans-serif; line-height: 1;">
                                    {heure_arrosage}
                                </div>
                                <div style="font-size: 14px; opacity: 0.7; margin-top: 5px;">
                                    🌱 Parcelle <b>{id_parc}</b> &nbsp;|&nbsp; 💧 Durée : <b>{duree_arrosage} s</b>
                                </div>
                            </div>
                            """
                            st.markdown(design_timeline, unsafe_allow_html=True)

                        with col_button_toggle:
                            # Une tâche est considérée comme "Bloquée" si:
                            # - Le système est en mode Auto
                            # - OU si l'heure de l'arrosage est déjà passée
                            maintenant = datetime.now()

                            # On transforme les textes (String) en vrais objets Date et Heure
                            # On utilise l'heure formatée précédemment pour éviter l'erreur "unconverted data remains"
                            date_obj = datetime.strptime(str(date_arrosage_raw), '%Y-%m-%d').date()
                            heure_obj = datetime.strptime(heure_arrosage, '%H:%M').time()

                            moment_programme = datetime.combine(date_obj, heure_obj)
                            est_expiree = moment_programme < maintenant

                            # --------- Le TOGGLE de contrôle à OUI ou NON selon la base MySQL ----------- 
                            etat_actuel = True if (status == "Actif") else False

                            # --------- Pour capturer la valeur modifié de l'option "répéter tâche". Après que l'utilisateur a décidé de remettre à jour l'état actuel de l'option 
                            # pour le déclenchement de l'option "répéter tâche"
                            nouveau_status_planification = st.toggle(
                                "", 
                                value=etat_actuel, 
                                key=f"tgl_{id_planning}", 
                                disabled=(mode_systeme_actuel == "Auto") or est_expiree, 
                                on_change=changer_status_planification,
                                args=(id_planning, status, choix_parcelle)
                            ) 
                                                      
                    # Ligne de séparation très discrète entre chaque alarme
                    st.markdown("<hr style='margin: 5px 0px 15px 0px; opacity: 0.2;'>", unsafe_allow_html=True)

        else:
            st.info("Aucune planification trouvée.")
    # ---------------------------------------- INTERFACE DU TABLEAU DE BORD STREAMLIT ------------------------------------------------
            
# ------------------------------------- INTERFACE DE L'ANALYSE DES DONNEES STREAMLIT ---------------------------------------------
elif selected == "Analyse des données":
    st.write(f"### TABLEAU RECAPITULATIF INFO CAPTEUR POUR {choix_parcelle}")
    
    response = requests.get(f"http://localhost:5000/select/donnee_capteur/id_parc={choix_parcelle}")
    data_json = response.json()

    df_info_capteur_texte = pd.DataFrame(
       data_json["liste_donnee_capteur"],
       columns=["Id_parc", "Humidite", "Temperature", "Luminosite", "Date_pub"]
    )

    # st.dataframe va nous servir d'affichage des info capteur dans une table
    st.dataframe(
        df_info_capteur_texte,
        column_config={
            "Id_parc": st.column_config.TextColumn("Parcelle", help="ID unique de la parcelle"),
            "Humidite": st.column_config.NumberColumn(
            "Niveau d'Humidité (%)"
            ),

            "Temperature": st.column_config.NumberColumn(
                "Temperature (°C)",
                format="%.1f"
            ),

            "Luminosite": st.column_config.NumberColumn(
                "Luminosite (lux)",
                format="%.2f"
            ),

            "Date_pub": st.column_config.TimeColumn("Publié le", format="YYYY/MM/DD"),
        },
        hide_index=True,
        use_container_width=True
    )

    df_info_capteur = pd.DataFrame(df_info_capteur_texte)
    # On va convertir le jeu de donnée Dataframe en jeu de donnée csv pour permettre à l'utilisateur de le télécharger par la suite
    # index = False pour indiquer à python d'ignorer la colonne de numérotation qui est rajouté par pandas par défaut (lors de la création du tableau -> le DataFrame)
    csv = df_info_capteur.to_csv(index=False).encode('utf-8')

    st.download_button(
        "Exporter rapport complet",
        data=csv,
        file_name=f"rapport_info_capteur.csv",
        mime="text/csv" # mime sert à indiquer au navigateur le type de document dont il a à télécharger (ex : ce document l'a s'agit d'un fichier csv ou autre)
    )

    st.markdown("""
                <style>
                    .main { background-color: #f8f9fa;}
                    .stMetric { border: 1px solid #e0e0e0; padding: 15px; border-radius: 12px; background: white; }
                </style>
            """, unsafe_allow_html=True)

    st.title("Suivi des données de capteur")

    st.markdown("---")

    def afficher_monitoring_temps_reel(choix_parcelle, df_info):
        
        # Injection du CSS et du JS
        html_markup = f"""
            <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
                
                <!-- Graphique 1 : Humidité -->
                <div style="background: #ffffff; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 25px;">
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.1rem;"> Humidité (%) - {choix_parcelle} <span style="color: #00d1b2; font-size: 0.8rem; float: right;">● LIVE</span></h3>
                    <div style="height: 250px;"><canvas id="chartHum"></canvas></div>
                </div>

                <!-- Graphique 2 : Température -->
                <div style="background: #ffffff; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 25px;">
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.1rem;"> Température (°C) - {choix_parcelle} <span style="color: #ff5722; font-size: 0.8rem; float: right;">● LIVE</span></h3>
                    <div style="height: 250px;"><canvas id="chartTemp"></canvas></div>
                </div>

                <!-- Graphique 3 : Luminosité -->
                <div style="background: #ffffff; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 20px;">
                    <h3 style="margin: 0 0 10px 0; color: #333; font-size: 1.1rem;"> Luminosité (lx) - {choix_parcelle} <span style="color: #ffc107; font-size: 0.8rem; float: right;">● LIVE</span></h3>
                    <div style="height: 250px;"><canvas id="chartLum"></canvas></div>
                </div>

            </div>

            <!-- Chargement des librairies -->
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/mqtt/5.14.1/mqtt.min.js"></script>

            <script>
                // Outil pratique pour générer rapidement la config des 3 graphiques
                function preparerConfigChart(nom, couleur, yMaxRecommande) {{
                    return {{
                        type: 'line',
                        data: {{
                            labels: [],
                            datasets: [{{
                                label: nom,
                                data: [],
                                borderColor: couleur,
                                borderWidth: 3,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: couleur,
                                pointRadius: 4,
                                fill: false,
                                tension: 0.3
                            }}]
                        }},
                        options: {{
                            responsive: true,
                            maintainAspectRatio: false,
                            layout: {{
                                padding: {{
                                    top: 15
                                }}
                            }},

                            plugins: {{ 
                                legend: {{ 
                                    display: false 
                                }} 
                            }},

                            scales: {{ 
                                x: {{ ticks: {{ maxRotation: 0 }} }},
                                y: {{ 
                                    beginAtZero: true, 
                                    suggestedMax: yMaxRecommande,
                                    grace: '20%'
                                }} 
                            }},

                            animation: {{ duration: 500 }}
                        }}
                    }};
                }}

                
                // 2. Instanciation des 3 Toiles (Canvas) avec les configurations vides
                const cHum = new Chart(document.getElementById('chartHum'), preparerConfigChart('Hum (%)', '#00d1b2', 20));
                const cTemp = new Chart(document.getElementById('chartTemp'), preparerConfigChart('Temp (°C)', '#ff5722', 35));
                const cLum = new Chart(document.getElementById('chartLum'), preparerConfigChart('Lum (lx)', '#ffc107', 50));

                // Connexion au broker MQTT via le canal websocket 
                const broker = 'ws://10.119.163.221:9001';
                const topic_humidite = 'data/humidite/{choix_parcelle}';
                const topic_temperature = 'data/temperature/{choix_parcelle}';
                const topic_luminosite = 'data/luminosite/{choix_parcelle}';

                const client_mqtt = mqtt.connect(broker);

                client_mqtt.on('connect', () => {{
                    client_mqtt.subscribe(topic_humidite);
                    client_mqtt.subscribe(topic_temperature);
                    client_mqtt.subscribe(topic_luminosite);
                    console.log("Graphiques vides prêts. En attente de données live MQTT de l'Arduino...");
                }});

                // A chaque fois qu'un message tombe, on l'assigne à la bonne courbe
                client_mqtt.on('message', (topic, message) => {{
                    try {{
                        // Conversion en document JSON le format du message reçu
                        const jsonData = JSON.parse(message.toString());

                        const now = new Date();
                        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');

                        // Injection conditionnelle dans la courbe de l'humidité
                        if (topic === topic_humidite && !isNaN(jsonData.humidite) && jsonData.humidite !== "error"){{
                            cHum.data.labels.push(timeStr);
                            cHum.data.datasets[0].data.push(jsonData.humidite);

                            if (cHum.data.labels.length > 15) {{ 
                                cHum.data.labels.shift(); 
                                cHum.data.datasets[0].data.shift(); 
                            }}

                            cHum.update();
                        }}
                        
                        // Injection conditionnelle dans la courbe de température
                        else if (topic === topic_temperature && !isNaN(jsonData.temperature) && jsonData.temperature !== "error"){{
                            // Rajouter l'heure actuelle (ex: '16:45:00') tout au bout à droite de l'axe horizontal (axe X) du graphique "cTemp"
                            cTemp.data.labels.push(timeStr);

                            // Rajouter la vraie valeur du capteur de température (ex: 250 lx) tout au bout à droite de la ligne du graphique "cTemp"
                            // (pour qu'elle corresponde à la nouvelle heure qu'on vient d'ajouter.)
                            cTemp.data.datasets[0].data.push(jsonData.temperature);

                            // Cette condition est celui qui permet de basculer dynamiquement la variation de la courbe dans le graphe à chaque fois que le nombre
                            // de point qui représente la courbe dépasse 15 points total dans le graphe. Le premier (c'est-à-dire le point le plus ancien s'efface et se remplace)
                            // par un nouveau point qui vient se placer au tout début du graphe. Pour émettre du "Temps réel"
                            if (cTemp.data.labels.length > 15) {{ 
                                cTemp.data.labels.shift(); 
                                cTemp.data.datasets[0].data.shift(); 
                            }}

                            // C'est pour dire au moteur "Chart.js" de repeindre physiquement la toile (le Canvas) sur l'écran de l'utilisateur 
                            // pour qu'il voie la nouvelle image du courbe dans le graphe.
                            cTemp.update();
                        }}

                        // Injection conditionnelle dans la courbe de luminosité
                        else if (topic === topic_luminosite && !isNaN(jsonData.luminosite) && jsonData.luminosite !== "error"){{
                            cLum.data.labels.push(timeStr);
                            cLum.data.datasets[0].data.push(jsonData.luminosite);

                            if (cLum.data.labels.length > 15) {{
                                cLum.data.labels.shift();
                                cLum.data.datasets[0].data.shift();
                            }}

                            cLum.update();
                        }}

                    }} catch(err){{
                        // Ignorer les messages corrompus pour éviter que les graphiques ne plantent
                        console.error("Trame MQTT invalide ignorée :", err);
                    }}
                }});
            </script>
        """

        st.components.v1.html(html_markup, height=1100)

    with st.container(border=True):
        mode = st.radio("Mode d'affichage", ["Temps Réel", "Historique"])

        if mode == "Temps Réel":
            date_selectionnee = st.date_input("-- Choisir une date pour l'analyse --", datetime.now())
            afficher_monitoring_temps_reel(choix_parcelle, df_info_capteur_texte)
        
        elif mode == "Historique":
            response = requests.get(f"http://localhost:5000/select/donnee_capteur/id_parc={choix_parcelle}")

# ------------------------------------- INTERFACE DE L'ANALYSE DES DONNEES STREAMLIT ---------------------------------------------

