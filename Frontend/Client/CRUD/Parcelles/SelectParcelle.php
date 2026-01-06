<?php

require_once (__DIR__.'/../../../Config/MySQL.php');

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlRequestUpdate = "UPDATE info_parc
                        LEFT JOIN reservation_parc
                        ON info_parc.Id_res = reservation_parc.Id_res
                        SET Status_parc = 'dispo'
                        WHERE (info_parc.Id_res IS NULL OR info_parc.Id_res IS NOT NULL) 
                        AND (Status_res = 'attente' OR Status_res = 'refus')";
            
            $dbRequestUpdate = $mysqlClient->prepare($sqlRequestUpdate);

            $dbRequestUpdate->execute();

            $sqlRequestUpdateOnOccupe = "UPDATE info_parc
                                INNER JOIN reservation_parc
                                ON info_parc.Id_parc = reservation_parc.Id_parc
                                SET Status_parc = 'occupe'
                                WHERE Status_res = 'valide'";

            $dbRequestUpdateOnOccupe = $mysqlClient->prepare($sqlRequestUpdateOnOccupe);

            $dbRequestUpdateOnOccupe->execute();

            $sqlQuery = "SELECT * FROM info_parc";
            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
                
            $parcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>