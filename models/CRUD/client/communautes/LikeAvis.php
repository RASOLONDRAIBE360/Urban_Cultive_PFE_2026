<?php
session_start();
require_once (__DIR__.'/../../../../config/MySQL.php');

$id_parc = $_POST['id_parc'];
$id_avis = $_POST['id_avis'];
$user_id = $_SESSION['user_id_user'];
$type_action = $_POST['type_action']; // 'like' ou 'dislike'

try {
    $mysqlClient = new PDO(
        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );
    $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Vérifier l'action actuelle de l'utilisateur sur cet avis
    $sql = "SELECT Type_action 
            FROM like_avis 
            WHERE Id_avis = :id_avis 
            AND Id_parc = :id_parc 
            AND User_id = :user_id";

    $stmt = $mysqlClient->prepare($sql);

    $stmt->execute([
        ':id_avis' => $id_avis,
        ':id_parc' => $id_parc,
        ':user_id' => $user_id,
    ]);

    $currentAction = $stmt->fetchColumn();

    // 2. Si une action existe, la supprimer
    if ($currentAction !== false) {
        $sqlDelete = "DELETE FROM like_avis 
                      WHERE Id_avis = :id_avis 
                      AND Id_parc = :id_parc 
                      AND User_id = :user_id";

        $stmt = $mysqlClient->prepare($sqlDelete);

        $stmt->execute([
            ':id_avis' => $id_avis,
            ':id_parc' => $id_parc,
            ':user_id' => $user_id,
        ]);
    }

    // 3. Si l'action actuelle est différente de la nouvelle, insérer la nouvelle
    if ($currentAction !== $type_action) {
        $sqlInsert = "INSERT INTO like_avis (User_id, Id_parc, Id_avis, Type_action) 
                      VALUES (:user_id, :id_parc, :id_avis, :type_action)";

        $stmt = $mysqlClient->prepare($sqlInsert);
        
        $stmt->execute([
            ':user_id' => $user_id,
            ':id_parc' => $id_parc,
            ':id_avis' => $id_avis,
            ':type_action' => $type_action,
        ]);
    }

    // 4. Rafraîchir les avis
    $sqlAvis = "SELECT avis.Id_avis, avis.Id_parc, Avis, Date,
                       SUM(CASE WHEN Type_action = 'like' THEN 1 ELSE 0 END) AS NumberLike,
                       SUM(CASE WHEN Type_action = 'dislike' THEN 1 ELSE 0 END) AS NumberDislike
                FROM avis
                LEFT JOIN like_avis ON avis.Id_avis = like_avis.Id_avis
                WHERE avis.Id_parc = :id_parc
                GROUP BY avis.Id_avis";

    $stmt = $mysqlClient->prepare($sqlAvis);
    $stmt->execute([':id_parc' => $id_parc]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['Avis'] = $results;

    header('Location: ../../../../views/client/site_web_user/Avis.php');
    exit;

} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>
