<?php

session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_POST['id_parc'] ?? null;

if(empty($id_parc)){
    echo "La variable id_parc n'est pas défini";
} else {
        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT Nom_parc, Taille_parc, Prix_parc, Duree_res
            FROM info_parc 
            INNER JOIN reservation_parc
            ON info_parc.id_parc = reservation_parc.id_parc
            WHERE reservation_parc.Id_parc = :id_parc";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                'id_parc' => $id_parc,
            ]);
            
            $ThisParcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['ThisParcelles'] = $ThisParcelles;
            $_SESSION['id_parc'] = $id_parc;
        
            if(!empty($ThisParcelles)){
                header('Location: ../../GestionReservation/RenouvellementReservation.php');
                exit;
            } else{
                header("Location: ../../Site_web_user/Parcelle.php?showModal={$id_parc}");
                exit;
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }
    }
?>