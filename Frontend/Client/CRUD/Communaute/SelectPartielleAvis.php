<?php

require_once (__DIR__.'/../../../Config/MySQL.php');

$user_id = $_SESSION['user_id_user'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT Id_parc 
                    FROM reservation_parc 
                    WHERE User_id = :user_id 
                    AND Status_res = 'valide' 
                    AND Date_fin > CURDATE()";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':user_id' => $user_id,
            ]);
                
            $listeUtilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>