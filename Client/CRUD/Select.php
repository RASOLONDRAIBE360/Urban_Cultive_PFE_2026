<?php

if (!isset($_SESSION['nom'], $_SESSION['prenom'], $_SESSION['email'])) {
    die('Erreur : Les variables de session ne sont pas définies.');
}

require_once (__DIR__.'/../../Config/MySQL.php');

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlRequest = "SELECT * FROM users WHERE Nom = :nom AND Prenom = :prenom AND Email = :email";
            $dtoStatement = $mysqlClient->prepare($sqlRequest);
            $dtoStatement->execute([
                ':nom'=>$_SESSION['nom'],
                ':prenom'=>$_SESSION['prenom'],
                ':email'=>$_SESSION['email'],
            ]);

            $users = $dtoStatement->fetchAll(PDO::FETCH_ASSOC);
    
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }
        

?>