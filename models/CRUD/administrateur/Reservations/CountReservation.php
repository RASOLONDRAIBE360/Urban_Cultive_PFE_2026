<?php
require_once (__DIR__.'/../../../../config/MySQL.php');


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

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>