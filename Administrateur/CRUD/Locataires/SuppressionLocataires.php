<?php

session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');

$email = $_POST['email'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlSelectId = "SELECT User_id FROM users WHERE Email = :email";

            $dbprepare = $mysqlClient->prepare($sqlSelectId);

            $dbprepare->execute([
                ':email' => $email,
            ]);

            $userId = $dbprepare->fetchColumn();
            
            $sqlQuery = "DELETE FROM users WHERE User_id = :userId";
            
            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute([
                ':userId' => $userId,
            ]);

            if ($dbprepare->rowCount() > 0) {
                $_SESSION['successSuppression'] = 'Utilisateur supprimé avec succès.';
                echo "<script>window.location.href = '../../Site_web_admin/Accueil.php';</script>";
            } else {
                $_SESSION['erreurSuppression'] = 'Aucun utilisateur trouvé.';
                echo "<script>window.location.href = '../../Site_web_admin/Accueil.php';</script>";
                   }
            
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>