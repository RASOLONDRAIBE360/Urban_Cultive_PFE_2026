<?php

session_start();

require_once (__DIR__.'/../../../../config/MySQL.php');

$id_avis = $_POST['id_avis'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlSelectId = "SELECT Id_avis FROM avis WHERE Id_avis = :id_avis";

            $dbprepare = $mysqlClient->prepare($sqlSelectId);

            $dbprepare->execute([
                ':id_avis' => $id_avis,
            ]);

            $userId = $dbprepare->fetchColumn();
            
            $sqlQuery = "DELETE FROM avis WHERE Id_avis = :id_avis";
            
            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute([
                ':id_avis' => $id_avis,
            ]);

            if ($dbprepare->rowCount() > 0) {
                $_SESSION['successDeleteAvis'] = 'Avis supprimé avec succès.';
            } else {
                $_SESSION['erreurDeleteAvis'] = 'Aucun avis trouvé.';
            }
            
            header('Location: ../../../../views/administrateur/Site_web_admin/Avis.php');
            exit;

            
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }

?>