<?php

session_start();

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

        $targetDir = "../../../Upload/"; // Dossier où stocker les images

        $fileName = $_FILES["file"]["name"];
        $targetFilePath = $targetDir . $fileName;
        $cheminWeb = '../../Upload/' . $fileName; // Chemin WEB (à stocker en base)
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Vérifier si le fichier est bien une image
        $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp", "avif"];
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['erreurInsertPicture'] = "Seuls les fichiers JPG, JPEG, PNG, GIF, WEBP et AVIF sont autorisés.";
            echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=1';</script>";
            exit();
        }

        // Déplacer le fichier uploadé
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                // Insérer le chemin dans la base de données

            $sqlRequest = "UPDATE info_parc 
                        SET Taille_parc = :taille_parc, Nom_parc = :nom_parc, Prix_parc = :prix_parc, Status_parc = :status_parc, Exposition = :expo, Equipements = :equip, Preferences = :pref, Description = :descrip, Chemin_image = :cheminWeb 
                        WHERE Id_parc = :id_parc";

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
                ':cheminWeb'=>$cheminWeb,
                ':id_parc' => $id_parc,
            ]);
                
            if ($pdoStatement->rowCount() > 0){
                $_SESSION['successUpdate'] = "Modification du champ réussie.";
                echo '<script>window.location.href="../../Site_web_admin/Parcelle.php?showModal=1";</script>';
                exit();
            } else {
                $_SESSION['erreurUpdate'] = "Modification du champ échouée.";
                echo '<script>window.location.href="../../Site_web_admin/Parcelle.php?showModal=1";</script>';
                exit();
            }
        } else {
            $_SESSION['erreurInsertPicture'] = "Erreur survenu lors de l'ajout de l'image.";
            echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=1';</script>";
            exit();
        }

    }catch(Exception $exception){
        die('Erreur :'. $exception->getMessage());
    }
?>