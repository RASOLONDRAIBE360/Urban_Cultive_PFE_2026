<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$email = $_POST['email'] ?? null;
$date = $_POST['date'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "DELETE FROM commentaire WHERE Email = :email AND Date = :date";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':email' => $email,
                ':date' => $date,
            ]);

            if ($dbprepare->rowCount() > 0) {
                echo "<script> window.location.href = '../Commentaire.php';</script>";
            } else {
                echo "<script> alert('Erreur lors de la tentative de suppression du commentaire.'); window.location.href = '../Commentaire.php';</script>";
                   }
            

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>