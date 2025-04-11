<?php
require_once (__DIR__.'/../../Config/MySQL.php');

session_start();   

$user_id = $_SESSION['user_id'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * FROM avis WHERE User_id = :user_id";
            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':email' => $email,
                ':user_id' => $user_id,
            ]);
                
            $listeUtilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>