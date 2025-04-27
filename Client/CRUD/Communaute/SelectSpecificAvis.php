<?php

session_start();
require_once (__DIR__.'/../../../Config/MySQL.php');

// Vérification des variables POST et SESSION
if (!isset($_POST['id_parc']) || !isset($_SESSION['user_id_user'])) {
    die("Erreur : Données manquantes.");
}

$id_parc = $_POST['id_parc'];
$user_id = $_SESSION['user_id_user'];

try {
    // Connexion à la base de données
    $mysqlClient = new PDO(
        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );

    $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération des avis
    $sqlQuery = "SELECT * 
        FROM avis 
        WHERE Id_parc = :id_parc";

    $dbprepare = $mysqlClient->prepare($sqlQuery);
    $dbprepare->execute([
        ':id_parc' => $id_parc
    ]);

    $Avis = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['Avis'] = $Avis;

    // Vérification de la présence d'avis
    if (empty($Avis)) {
        $_SESSION['infoAvis'] = "Aucun avis pour l'instant";
    } else {
        // Récupération des likes et dislikes en une seule requête
        $sqlRequestVerifAvis = "SELECT 
            SUM(CASE WHEN Type_action = 'like' THEN 1 ELSE 0 END) AS NumberLike,
            SUM(CASE WHEN Type_action = 'dislike' THEN 1 ELSE 0 END) AS NumberDislike
        FROM like_avis WHERE Id_parc = :id_parc";

        $pdoStatement = $mysqlClient->prepare($sqlRequestVerifAvis);
        $pdoStatement->execute([':id_parc' => $id_parc]);

        $result = $pdoStatement->fetch(PDO::FETCH_ASSOC);

        // Mise à jour des valeurs de session avec des valeurs par défaut pour éviter NULL
        $_SESSION['NumberLike'] = $result['NumberLike'] ?? 0;
        $_SESSION['NumberDislike'] = $result['NumberDislike'] ?? 0;
    }

    // Redirection après l'exécution
    header('Location: ../../Site_web_user/Avis.php');
    exit;

} catch (Exception $exception) {
    die('Erreur : ' . $exception->getMessage());
}

?>