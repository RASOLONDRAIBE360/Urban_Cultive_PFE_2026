<?php

session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_POST['id_parc'] ?? null;

if(empty($id_parc)){
    echo "La variable id_parc n'est pas défini";
} else{
        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT Nom_parc, Taille_parc, Prix_parc
            FROM info_parc 
            WHERE Id_parc = :id_parc";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                'id_parc' => $id_parc,
            ]);
            
            $MyParcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['MyParcelles'] = $MyParcelles;
            $_SESSION['id_parc'] = $id_parc;
        
            if(!empty($MyParcelles)){
                header('Location: ../../GestionReservation/FormulaireReservation.php');
                exit;
            } else{
                header('Location: ../../Accueil.php?showModal=1');
                exit;
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }
    }
?>