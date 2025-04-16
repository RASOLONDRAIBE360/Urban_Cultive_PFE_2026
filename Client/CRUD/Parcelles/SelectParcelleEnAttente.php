<?php

require_once (__DIR__.'/../../../Config/MySQL.php');
$id_res = $_SESSION['id_res'] ?? null; 

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT users.User_id, Id_res, info_parc.Id_parc, Taille_parc, Prix_parc, Status_parc, Exposition, Equipements, Preferences, Description
                    FROM reservation_parc 
                    INNER JOIN users ON reservation_parc.User_id = users.User_id
                    INNER JOIN info_parc ON reservation_parc.Id_res = info_parc.Id_res 
                    WHERE users.User_id = :user_id 
                    AND Status_res = 'attente' 
                    AND Date_fin >= NOW()
                    AND Date_res <= NOW()";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
                
            $parcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['parcelles'] = $parcelles;

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>