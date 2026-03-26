<?php

require_once (__DIR__.'/../../../../config/MySQL.php');

$user_id = $_SESSION['user_id_user'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * 
                    FROM avis 
                    WHERE User_id = :user_id";
            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':user_id' => $user_id,
            ]);
                
            $utilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>