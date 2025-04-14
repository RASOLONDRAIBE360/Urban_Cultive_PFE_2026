<?php
session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');

$email = $_SESSION['email'] ?? null;
$date = $_POST['date'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlQuery = "DELETE FROM avis WHERE User_id = :user_id AND Date = :date";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':user_id' => $user_id,
                ':date' => $date,
            ]);

            if ($dbprepare->rowCount() > 0) {
                echo "<script> window.location.href = '../../Communaute.php';</script>";
            } else {
                echo "<script> alert('Erreur lors de la tentative de suppression du commentaire.'); window.location.href = '../../Communaute.php';</script>";
                   }
            

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>