<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_POST['id_parc'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "DELETE FROM info_parc WHERE Id_parc = :id_parc";
            
            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute([
                ':id_parc' => $id_parc,
            ]);

            if ($dbprepare->rowCount() > 0) {
                echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php';</script>";
            } else {
                echo "<script> alert('Erreur lors de la tentative de suppression de parcelle.'); window.location.href = '../../Site_web_admin/Parcelle.php';</script>";
                   }
            
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>