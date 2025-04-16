<?php

require_once (__DIR__.'/../../../Config/MySQL.php');

$user_id = $_SESSION['user_id'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT info_parc.Id_parc, Taille_parc, Prix_parc, Status_parc, Exposition, Equipements, Preferences, Description
                    FROM reservation_parc 
                    INNER JOIN users ON reservation_parc.User_id = users.User_id
                    INNER JOIN info_parc ON reservation_parc.Id_res = info_parc.Id_res 
                    WHERE users.User_id = :user_id 
                    AND Status_res = 'valide' 
                    AND Date_fin >= NOW()
                    AND Date_res <= NOW()";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':user_id' => $user_id,
            ]);
                
            $Parcelles1 = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['Parcelles1'] = $Parcelles1;

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>