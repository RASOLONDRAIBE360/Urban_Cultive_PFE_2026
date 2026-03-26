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
            
            $sqlQuery = "SELECT info_parc.Id_parc, Taille_parc, Prix_parc, Status_parc, Status_res, Exposition, Equipements, Preferences, Description, Chemin_image, Date_fin, Date_res, Date_limite
                    FROM info_parc 
                    INNER JOIN reservation_parc 
                    ON info_parc.Id_parc = reservation_parc.Id_parc 
                    WHERE User_id = :user_id 
                    AND Status_res = 'valide'";

            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute([
                ':user_id' => $user_id,
            ]);

            $MyParcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
            $_SESSION['MyParcelles'] = $MyParcelles;

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>