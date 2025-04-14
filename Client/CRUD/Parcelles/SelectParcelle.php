<?php

require_once (__DIR__.'/../../../Config/MySQL.php');

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlRequest = "UPDATE info_parc 
                        LEFT JOIN reservation_parc ON info_parc.Id_parc = reservation_parc.Id_parc
                        SET Status_parc = 'dispo'
                        WHERE Status_res = 'attente' 
                        OR Status_res = 'refus' 
                        OR info_parc.Id_res is null";

            $pdoStatement = $mysqlClient->prepare($sqlRequest);

            $pdoStatement->execute();

            $sqlQuery = "SELECT * FROM info_parc";
            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
                
            $parcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['parcelles'] = $parcelles;

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>