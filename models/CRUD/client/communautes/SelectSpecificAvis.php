<?php

session_start();
require_once (__DIR__.'/../../../../config/MySQL.php');

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

    // Récupération des avis avec l'email de l'auteur
    $sqlQuery = "SELECT avis.*, users.Email 
        FROM avis 
        INNER JOIN users ON avis.User_id = users.User_id
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
        $sqlRequestVerifAvis = "SELECT avis.Id_avis, avis.Id_parc, Avis, Date, users.Email,
            SUM(CASE WHEN Type_action = 'like' THEN 1 ELSE 0 END) AS NumberLike,
            SUM(CASE WHEN Type_action = 'dislike' THEN 1 ELSE 0 END) AS NumberDislike
        FROM avis
        INNER JOIN users
        ON avis.User_id = users.User_id
        LEFT JOIN like_avis
        ON avis.Id_avis = like_avis.Id_avis
        WHERE avis.Id_parc = :id_parc
        GROUP BY avis.Id_avis, avis.Id_parc, Avis, Date, users.Email";

        $pdoStatement = $mysqlClient->prepare($sqlRequestVerifAvis);
        $pdoStatement->execute([
            ':id_parc' => $id_parc,
        ]);

        $results = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        $_SESSION['Avis'] = $results;
    }

    // Redirection après l'exécution
    header('Location: ../../../../views/client/site_web_user/Avis.php');
    exit;

} catch (Exception $exception) {
    die('Erreur : ' . $exception->getMessage());
}

?>