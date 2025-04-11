<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_POST['id_parc'] ?? null;
$taille_parc = $_POST['taille_parc'] ?? null;
$nom_parc = $_POST['nom_parc'] ?? null;
$prix_parc = $_POST['prix_parc'] ?? null;
$status_parc = $_POST['status_parc'] ?? null;
$equip = $_POST['equip'];
$expo = $_POST['expo'];
$pref = $_POST['pref'];
$descrip = $_POST['descrip'];

    try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "INSERT INTO info_parc (Id_parc, Taille_parc, Nom_parc, Prix_parc, Status_parc, Exposition, Equipements, Preferences, Description) VALUES (:id_parc, :taille_parc, :nom_parc, :prix_parc, :status_parc, :expo, :equip, :pref, :descrip)";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':id_parc' => $id_parc,
                ':taille_parc' => $taille_parc,
                ':nom_parc' => $nom_parc,
                ':prix_parc' => $prix_parc,
                ':status_parc' => $status_parc,
                ':equip'=>$equip,
                ':expo'=>$expo,
                ':pref'=>$pref,
                ':descrip'=>$descrip,
            ]);

            if ($dbprepare->rowCount() > 0) {
                echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php';</script>";
            } else {
                echo "<script> alert('Erreur survenu lors de l'ajout du nouvelle parcelle.'); window.location.href = '../../Site_web_admin/Parcelle.php';</script>";
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }     

?>