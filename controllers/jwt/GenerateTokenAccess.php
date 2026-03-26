<?php
session_start();

require_once (__DIR__.'/../../models/CRUD/client/reservations/SelectParcelleOnReservation.php');
// 1. Inclusion de l'autoloader Composer pour Firebase JWT afin de générer un token 
// d'accès sécurisé pour mon script python pour la récupération des données de la
// session PHP
require_once (__DIR__.'/../../vendor/autoload.php');

// 2. Inclusion de la classe JWT du librairie Firebase JWT nécessaire pour la génération
// d'un token sécurisé
use Firebase\JWT\JWT;


// 3. Sécurité : On vérifie que l'utilisateur est bien connecté en PHP
if (!isset($_SESSION['user_id_user'])) {
    header("Location: ../../views/client/login/FormulaireConnexion.php");
} else {
    $secret_key = "my_secret_key_pfe_gestion_participative_parcelle_urbaine_2026";

    // 4. Préparation des données (Payload)
    //payload : est la charge utile (les données) que l'on souhaite encoder dans le token JWT (JSON Web Token)
    $payload = [
        "iss" => "streamlit-app", //Pour spécifier l'émetteur du token (pour indiquer celui qui a généré le token)
        "sub" => "user_id_user", //Pour spécifier l'utilisateur ou l'entité concerné par le token (c'est ce qui permet de savoir à qui appartient le token)
        "parcelles" => $_SESSION['parcellesOnReservation'], // On récupère les données de la session PHP
        "user_id_user" => $_SESSION['user_id_user'], // On récupère l'ID de l'utilisateur connecté
        "iat" => time(), // Pour spécifier la date de création du token
        "exp" => time() + 30
    ];

    //5. Génération du token JWT
    $token = JWT::encode($payload, $secret_key, 'HS256');

    /*
        __DIR__ : c'est pour obtenir le chemin absolu du dossier où se trouve mon fichier php actuellement

        realpath() : c'est une fonction qui permet d'assembler logiquement le chemin absolu obtenu de "DIR" avec le chemin relatif qui suit
        afin de pointer vers l'emplacement exacte du dossier rechercher.
    */
    $base_path = realpath(__DIR__."/../../venv_temp/Scripts/");

    $racine_projet = "C:/xampp/htdocs/Document_PFE";

    $python_exec = $base_path."/python.exe";
    $streamlit_exec = $base_path."/streamlit.exe"; 

    $flask_path = "controllers.client.tableau_de_bord";
    $streamlit_path = __DIR__."/../../views/client/tableau_de_bord/Controle.py";

    // 1. Lancer le serveur Flask en arrière-plan avec la machine virtuelle de manière asynchrone (ne bloque pas la page avec la fonction pclose(popen()))
    /*
        On a mis des guillemets textuelles \"\" pour indiquer au terminal de windows que le titre de la fenetre est ""
        et celui qui suit est le chemin absolu de l'executable python et le chemin absolu du fichier python 
    */

    function isServerRunning($port){
        $connection = @fsockopen(
            "localhost", // $hostname -> nom de domaine ou adresse IP du serveur 
            $port, // $port -> numéro du port sur lequel le serveur écoute
            $errno, // $errno -> numéro d'erreur
            $errstr, // $errstr -> message d'erreur
            1 // $timeout -> durée maximale d'attente pour la connexion (en secondes)
        );

        if ($connection) {
            fclose($connection);
            return true;
        } else {
            return false;
        }
    }

    //Condition pour vérifier si le serveur Flask est déjà en cours d'exécution ou pas
    /*
        Si le serveur Flask n'est pas en cours d'exécution alors on le lance

        popen() -> pour lancer un processus sur windows 

        start "" -> pour indiquer à windows la création d'une nouvelle processus indépendant nommé "" qui sera détaché 
        du programme principal déjà en cours de lancement.

        Cela évitera donc de bloquer le fonctionnement du processus qui est déjà en cours de lancement. 
    */
    if(!isServerRunning(5000)){
        /*
            On utilise /D pour dire à "start" de se placer dans le bon dossier avant de lancer Python
        */
        popen("start /D \"$racine_projet \" \"\" $python_exec -m $flask_path", "r");
    }
                    
    // 2. Lancer le serveurStreamlit en mode SILENCIEUX (headless) avec la machine virtuelle de python de manière asynchrone aussi
    /*
        Si le serveur Streamlit n'est pas en cours d'exécution alors on le lance

        --server.headless true -> pour indiquer au service de ne pas ouvrir automatiquement une fenêtre graphique (navigateur) après
        le lancement du script
    */
    if(!isServerRunning(8501)){
        popen("start \"\" $streamlit_exec run $streamlit_path --server.headless true", "r");
    }

    echo json_encode($_SESSION['parcellesOnReservation']);
    
    //Envoie des données (liste des parcelles) au script python depuis mon fichier PHP
    header("Location: http://localhost:8501/?token=$token");
    exit;
}



?>