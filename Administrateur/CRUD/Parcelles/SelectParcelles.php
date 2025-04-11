<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * FROM info_parc";

            $dbprepare = $mysqlClient->prepare($sqlQuery);
                                
            $dbprepare->execute();
                
            $parcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['parcelles'] = $parcelles;

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }

?>