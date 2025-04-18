<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT info_parc.Id_parc, reservation_parc.Id_res, users.User_id, Taille_parc, Nom_parc, Prix_parc, Status_parc, Exposition, Equipements, Preferences, Description, Chemin_image
                FROM info_parc 
                LEFT JOIN reservation_parc ON info_parc.Id_parc = reservation_parc.Id_parc
                LEFT JOIN users ON reservation_parc.User_id = users.User_id
                ORDER BY info_parc.Id_parc ASC";

            $dbprepare = $mysqlClient->prepare($sqlQuery);
                                
            $dbprepare->execute();
                
            $parcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['parcelles'] = $parcelles;

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }

?>