<?php
require_once (__DIR__.'/../../../Config/MySQL.php');


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlRequest = "SELECT reservation_parc.Id_res, Email, Numero_tel, reservation_parc.Id_parc, Nom_parc, Taille_parc, Prix_parc, Date_res, Duree_res, Date_fin, Status_res 
            FROM reservation_parc 
            INNER JOIN info_parc, users 
            WHERE reservation_parc.Id_parc = info_parc.Id_parc 
            AND reservation_parc.User_id = users.User_id 
            AND Status_res = 'attente'
            ORDER BY Date_fin ASC";

            $pdoStatement = $mysqlClient->prepare($sqlRequest);

            $pdoStatement->execute();

            $reservations = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>