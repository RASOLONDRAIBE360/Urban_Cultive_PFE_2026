<?php

require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_SESSION['id_parc'] ?? null;


    if(empty($id_parc)){
        echo "La variable id_parc n'est pas défini";
    } else{

            try {
                $mysqlClient = new PDO(
                        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                                MYSQL_USER,
                                MYSQL_PASSWORD
                                    );

                $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sqlRequest = "SELECT Id_res FROM reservation_parc WHERE Id_parc = ?";
                $dtoStatement = $mysqlClient->prepare($sqlRequest);

                $dtoStatement->execute([
                    $id_parc,
                ]);
                
                $MyParcelles1 = $dtoStatement->fetchAll(PDO::FETCH_ASSOC);
                
                foreach($MyParcelles1 as $MyParcelle1){
                    $_SESSION['id_res'] = $MyParcelle1['Id_res'];
                }   

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        }

?>