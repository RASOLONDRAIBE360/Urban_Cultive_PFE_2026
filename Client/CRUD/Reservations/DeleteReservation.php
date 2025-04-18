<?php
session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_POST['id_parc'] ?? null;
$user_id = $_SESSION['user_id_user'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlQuery = "DELETE FROM reservation_parc WHERE Id_parc = :id_parc AND User_id = :user_id";
            
            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute([
                ':id_parc' => $id_parc,
                ':user_id' => $user_id,
            ]);

            if ($dbprepare->rowCount() > 0) {
                echo "<script>window.location.href = '../../Site_web_user/Parcelle.php';</script>";
            } else {
                $_SESSION['erreurAnnulation'] = "Erreur survenu lors de l'annulation de la réservation.";
                echo "<script>window.location.href = '../../Site_web_user/Parcelle.php';</script>";
                   }
            
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>