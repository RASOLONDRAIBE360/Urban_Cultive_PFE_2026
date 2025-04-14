<?php
require_once (__DIR__.'/../../../Config/MySQL.php');


$taille_parc = $_POST['taille_parc'];
$nom_parc = $_POST['nom_parc'];
$prix_parc = $_POST['prix_parc'];
$status_parc = $_POST['status_parc'];
$equip = $_POST['equip'];
$expo = $_POST['expo'];
$pref = $_POST['pref'];
$descrip = $_POST['descrip'];
$id_parc = $_POST['id_parc'];

    try{

        $MysqlClient = new PDO(
            sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
            MYSQL_USER,
            MYSQL_PASSWORD
        );

        $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sqlRequest = "UPDATE info_parc SET Taille_parc = :taille_parc, Nom_parc = :nom_parc, Prix_parc = :prix_parc, Status_parc = :status_parc, Exposition = :expo, Equipements = :equip, Preferences = :pref, Description = :descrip WHERE Id_parc = :id_parc";
        $pdoStatement = $MysqlClient->prepare($sqlRequest);
        $pdoStatement->execute([
            ':taille_parc'=>$taille_parc,
            ':nom_parc'=>$nom_parc,
            ':prix_parc'=>$prix_parc,
            ':status_parc'=>$status_parc,
            ':equip'=>$equip,
            ':expo'=>$expo,
            ':pref'=>$pref,
            ':descrip'=>$descrip,
            ':id_parc' => $id_parc,
        ]);
            
        if ($pdoStatement->rowCount() > 0){
            echo '<script>window.location.href="../../Site_web_admin/Parcelle.php";</script>';
        } else {
            echo '<script>alert("Modification du champ échouée."); window.location.href="../../Site_web_admin/Parcelle.php";</script>';
        }

    }catch(Exception $exception){
        die('Erreur :'. $exception->getMessage());
    }
?>