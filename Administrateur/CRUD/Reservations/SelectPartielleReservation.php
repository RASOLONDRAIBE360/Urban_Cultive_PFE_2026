<?php

$id_res = $_SESSION['id_res'];

require_once (__DIR__.'/../../../Config/MySQL.php');


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlRequest = "SELECT Email 
            FROM users
            INNER JOIN reservation_parc ON users.User_id = reservation_parc.User_id
            WHERE reservation_parc.Id_res = :id_res";

            $pdoStatement = $mysqlClient->prepare($sqlRequest);

            $pdoStatement->execute([
                'id_res' => $id_res,
            ]);

            $Utilisateurs = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['Utilisateurs'] = $Utilisateurs;
            
            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>