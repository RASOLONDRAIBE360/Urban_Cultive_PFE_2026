<?php
require_once (__DIR__.'/../../Config/MySQL.php');


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT COUNT(*) FROM reservation_parc";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
                
            $count = $dbprepare->fetchColumn();

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>