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

            $sqlRequest = "UPDATE reservation_parc 
                        INNER JOIN info_parc 
                        ON reservation_parc.Id_parc = info_parc.Id_parc
                        SET Status_res = 'valide', Status_parc = 'occupe'
                        WHERE reservation_parc.Id_res = :id_res";

            $pdoStatement = $MysqlClient->prepare($sqlRequest);

            $pdoStatement->execute([
                ':id_res'=>$id_res,
            ]);
            
            $sqlRequestEmail = "SELECT Email 
            FROM users
            INNER JOIN reservation_parc 
            ON users.User_id = reservation_parc.User_id
            WHERE reservation_parc.Id_res = :id_res";

            $emailPrepare = $MysqlClient->prepare($sqlRequestEmail);

            $emailPrepare->execute([
                'id_res' => $id_res,
            ]);

            $Utilisateurs = $emailPrepare->fetchAll(PDO::FETCH_ASSOC);
        
            $_SESSION['Utilisateurs'] = $Utilisateurs;

            if ($pdoStatement->rowCount() > 0 && $emailPrepare->rowCount() > 0){
                $_SESSION['successValidation'] = "Reservation validé";
                echo '<script>window.location.href="../../Site_web_admin/Reservation.php?showModal=1";</script>';
                exit();
            } else {
                $_SESSION['erreurValidation'] = "Reservation déjà validé";
                echo '<script>window.location.href="../../Site_web_admin/Reservation.php?showModal=1";</script>';
                exit();
            }
        
        }catch(Exception $exception){
            die('Erreur :'. $exception->getMessage());
        }

?>