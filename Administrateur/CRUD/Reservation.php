<?php
require_once (__DIR__.'/../../Config/MySQL.php');


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT COUNT(*) FROM reservation_parc WHERE Status_res = 'attente'";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
                
            $count1 = $dbprepare->fetchColumn();

            $sqlRequest = "SELECT Id_res, Email, Numero_tel, Nom_parc, Taille_parc, Prix_parc, Date_res, Duree_res, Status_res FROM info_parc INNER JOIN reservation_parc WHERE info_parc.Id_parc = reservation_parc.Id_parc";

            $pdoStatement = $mysqlClient->prepare($sqlRequest);

            $pdoStatement->execute();

            $reservations = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>