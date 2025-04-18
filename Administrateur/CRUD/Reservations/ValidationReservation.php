<?php

session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');


$id_res = $_POST["id_res"];

        try{

            $MysqlClient = new PDO(
                sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                MYSQL_USER,
                MYSQL_PASSWORD
            );

            $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlSelectDateRes = "SELECT Date_res 
                            FROM reservation_parc
                            WHERE Id_res = :id_res";
            
            $pdoStatement = $MysqlClient->prepare($sqlSelectDateRes);

            $pdoStatement->execute([
                ':id_res'=>$id_res,
            ]);

            $dateRes = $pdoStatement->fetchColumn();

            if(strtotime($dateRes) > strtotime(date("Y-m-d", strtotime("+14 days")))){

                $_SESSION['erreurValidation'] = "La validation est inaccessible pour le moment car la date de réservation est supérieure à 14 jours.";
                echo '<script>window.location.href="../../Site_web_admin/Reservation.php?showModal=1";</script>';
                exit();

            } else{

                $sqlRequest = "UPDATE reservation_parc 
                            INNER JOIN info_parc 
                            ON reservation_parc.Id_parc = info_parc.Id_parc
                            SET Status_res = 'valide', Status_parc = 'occupe'
                            WHERE reservation_parc.Id_res = :id_res";

                $pdoStatement = $MysqlClient->prepare($sqlRequest);

                $pdoStatement->execute([
                    ':id_res'=>$id_res,
                ]);
                            
                if ($pdoStatement->rowCount() > 0){
                    $_SESSION['successValidation'] = "Reservation validé";
                    echo '<script>window.location.href="../../Site_web_admin/Reservation.php?showModal=1";</script>';
                    exit();
                } else {
                    $_SESSION['erreurValidation'] = "Reservation déjà validé";
                    echo '<script>window.location.href="../../Site_web_admin/Reservation.php?showModal=1";</script>';
                    exit();
                }
                
            }
        }catch(Exception $exception){
            die('Erreur :'. $exception->getMessage());
        }

?>