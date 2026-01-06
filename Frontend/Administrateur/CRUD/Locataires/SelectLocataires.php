<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT User_id, Nom, Prenom, Num_tel, Date_Naissance, Email, Role 
                    FROM users
                    WHERE Role = 'utilisateur'
                    ORDER BY User_id DESC";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
            
            $listeUtilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>