<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT Id_avis, avis.Id_parc, Nom, Prenom, Email, Avis 
                    FROM avis
                    INNER JOIN users
                    ON avis.User_id = users.User_id
                    INNER JOIN info_parc
                    ON avis.Id_parc = info_parc.Id_parc
                    WHERE Role = 'utilisateur'
                    ORDER BY Id_avis DESC";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
            
            $listeAvis = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>