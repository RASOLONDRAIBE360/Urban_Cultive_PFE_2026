<?php
require_once (__DIR__.'/../../Config/MySQL.php');


$id_res = $_POST["id_res"];

        try{

            $MysqlClient = new PDO(
                sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                MYSQL_USER,
                MYSQL_PASSWORD
            );

            $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlRequest = "UPDATE reservation_parc SET Status_res = 'valide' WHERE Id_res = :id_res";
            $pdoStatement = $MysqlClient->prepare($sqlRequest);
            $pdoStatement->execute([
                ':id_res'=>$id_res,
            ]);
             
            if ($pdoStatement->rowCount() > 0){
                echo '<script>window.location.href="../Site_web_admin/Reservation.php?showModal=1";</script>';
            } else {
                echo '<script>window.location.href="../Site_web_admin/Reservation.php?showModal=1";</script>';
            }
            
        }catch(Exception $exception){
            die('Erreur :'. $exception->getMessage());
        }

?>