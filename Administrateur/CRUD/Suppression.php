<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "DELETE FROM users WHERE Email = :email AND Mot_de_Passe = :password";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':email' => $email,
                ':password' => $password,
            ]);

            if ($dbprepare->rowCount() > 0) {
                echo "<script> alert('Commentaire supprimer avec succès'); window.location.href = '../Site_web_admin/Index.php';</script>";
            } else {
                echo "<script> alert('Erreur lors de la tentative de suppression du commentaire.'); window.location.href = '../Site_web_admin/Index.php';</script>";
                   }
            

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>