<?php

session_start();
require_once (__DIR__.'/../../../../config/MySQL.php');

$user_id = $_POST['user_id'];

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * FROM users WHERE User_id = :user_id";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                'user_id' => $user_id,
            ]);
            
            $listeUtilisateur = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
            $_SESSION['UDatas'] = $listeUtilisateur;
            $_SESSION['my_user_id'] = $user_id;
            
            if(!empty($listeUtilisateur)){
                echo '<script>window.location.href="../../../views/administrateur/Site_web_admin/Accueil.php?showModal=1";</script>';
                exit();
            } else{
                echo '<script>window.location.href="../../../views/administrateur/Site_web_admin/Accueil.php?showModal=1";</script>';
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>