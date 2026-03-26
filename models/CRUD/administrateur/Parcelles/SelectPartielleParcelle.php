<?php

session_start();
require_once (__DIR__.'/../../../../config/MySQL.php');

$id_parc = $_POST['id_parc'];

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * FROM info_parc WHERE Id_parc = :id_parc";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                'id_parc' => $id_parc,
            ]);
            
            $Uparcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
            $_SESSION['Uparcelles'] = $Uparcelles;
            $_SESSION['my_id_parc'] = $id_parc;
            
            if(!empty($Uparcelles)){
                echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Parcelle.php?showModal=1";</script>';
                exit();
            } else{
                echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Parcelle.php?showModal=1";</script>';
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>