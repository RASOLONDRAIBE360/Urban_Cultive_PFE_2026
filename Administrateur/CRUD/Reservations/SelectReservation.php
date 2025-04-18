<?php
require_once (__DIR__.'/../../../Config/MySQL.php');


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        

            $sqlRequest = "SELECT reservation_parc.Id_res, Email, reservation_parc.Id_parc, Nom_parc, Taille_parc, Prix_parc, Date_res, Duree_res, Date_fin, Status_res 
            FROM reservation_parc 
            INNER JOIN info_parc
            ON reservation_parc.Id_parc = info_parc.Id_parc
            INNER JOIN users 
            ON reservation_parc.User_id = users.User_id
            ORDER BY (Status_res = 'attente') DESC, Date_res ASC;";

            $pdoStatement = $mysqlClient->prepare($sqlRequest);

            $pdoStatement->execute();

            $reservations = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['reservations'] = $reservations;

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>