<?php
session_start();

require_once (__DIR__.'/../../../../config/MySQL.php');

$email = $_SESSION['email_user'] ?? null;
$date = $_POST['date'] ?? null;
$id_avis = $_POST['Id_avis'] ?? null;
$user_id = $_SESSION['user_id_user'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlQuery = "DELETE FROM avis 
                    WHERE User_id = :user_id 
                    AND Id_avis = :id_avis
                    AND Date = :date";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':user_id' => $user_id,
                ':id_avis' => $id_avis,
                ':date' => $date,
            ]);

            if ($dbprepare->rowCount() > 0) {
                $_SESSION['successSuppression'] = "Avis supprimé !";
                echo "<script> window.location.href = '../../../../views/client/site_web_user/Communaute.php';</script>";
            } else {
                $_SESSION['erreurSuppression'] = "Erreur survenu lors de la suppression de l'avis.";
                echo "<script>window.location.href = '../../../../views/client/site_web_user/Communaute.php';</script>";
                   }
            

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>