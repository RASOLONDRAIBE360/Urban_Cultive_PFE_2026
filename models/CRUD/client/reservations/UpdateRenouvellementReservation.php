<?php
session_start();
require_once (__DIR__.'/../../../../config/MySQL.php');

$id_parc = $_POST['id_parc'];
$duree = $_POST['duree'];
$date_debut = $_POST['date_debut'];
$user_id = $_SESSION['user_id_user'];

try{
    $MysqlClient = new PDO(
        sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );

    $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $currentDate = date("Y-m-d");

    if($date_debut < $currentDate){

        $_SESSION['erreurRenouvellement'] = "La date de début ne peut pas être dans le passé.";
        header('Location: ../../gestionReservation/RenouvellementReservation.php');
        exit;

    } else{

        $sqlRequest = "UPDATE reservation_parc
                SET Duree_res =:duree, Date_res =:date_debut
                WHERE Id_parc =:id_parc
                AND User_id =:user_id";

        $pdoStatement = $MysqlClient->prepare($sqlRequest);
        
        $pdoStatement->execute([
            ':id_parc'=>$id_parc,
            ':duree'=>$duree,
            ':date_debut'=>$date_debut,
            ':user_id'=>$user_id,
        ]);

        $sqlQuery = "UPDATE reservation_parc
                SET Date_fin = DATE_ADD(Date_res, INTERVAL Duree_res MONTH)
                WHERE Id_parc = :id_parc
                AND User_id =:user_id"; 

        $dbStatement = $MysqlClient->prepare($sqlQuery);

        $dbStatement->execute([
            ':id_parc'=> $id_parc,
            ':user_id'=> $user_id,
        ]);


        if($pdoStatement->rowCount() > 0 && $dbStatement->rowCount() > 0){

            $_SESSION['successRenewal'] = "Renouvellement effectué !";

        } else {

            $_SESSION['erreurRenewal'] = "Aucune modification apporté !";
            header("Location: ../../../../views/client/site_web_user/Parcelle.php?showModal={$id_parc}");
            exit;

        }

        header("Location: ../../../../views/client/site_web_user/Parcelle.php?showModal={$id_parc}");
        exit;

    }
    
    } catch(Exception $exception){
        $_SESSION['erreurRenewal'] = "Erreur technique : " . $exception->getMessage();
        header("Location: ../../../../views/client/site_web_user/Parcelle.php");
        exit();
    }

?>