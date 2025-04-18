<?php

session_start();
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
            
            $sqlRequest = "SELECT COUNT(*) FROM info_parc WHERE Id_parc = ?";

            $dbprepare = $mysqlClient->prepare($sqlRequest);

            $dbprepare->execute([
                $id_parc
            ]);

            $count = $dbprepare->fetchColumn();

            if ($count > 0) {
                $_SESSION['erreurId'] = "Cette parcelle existe déjà.";
                echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=2';</script>";
                exit;
            }

            $fileName = $_FILES["file"]["name"];
            $targetDirSystem = __DIR__ . '/../../../Upload/'; 
            $targetFilePathSystem = $targetDirSystem . $fileName;

            // Chemin WEB (à stocker en base)
            $cheminWeb = '../../Upload/' . $fileName;

            $fileType = strtolower(pathinfo($targetFilePathSystem, PATHINFO_EXTENSION));

            // Vérifier si le fichier est bien une image
            $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp", "avif"];
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['erreurInsertImage'] = "Seuls les fichiers JPG, JPEG, PNG, GIF, WEBP et AVIF sont autorisés.";
                echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=2';</script>";
                exit;
            }

            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePathSystem)) {
                // Insérer le chemin dans la base de données

                $sqlQuery = "INSERT INTO info_parc (Id_parc, Taille_parc, Nom_parc, Prix_parc, Status_parc, Exposition, Equipements, Preferences, Description, Chemin_image) 
                        VALUES (:id_parc, :taille_parc, :nom_parc, :prix_parc, :status_parc, :expo, :equip, :pref, :descrip, :cheminWeb)";

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
                    ':cheminWeb' => $cheminWeb,
                ]);

                if ($dbprepare->rowCount() > 0) {
                    $_SESSION['successCreate'] = "Parcelle ajouté avec succès !";
                    echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=2';</script>";
                    exit;
                } else {
                    $_SESSION['erreurCreate'] = "Erreur survenu lors de l'ajout du nouvelle parcelle.";
                    echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=2';</script>";
                    exit;
                }
            } else {
                    $_SESSION['erreurInsertImage'] = "Erreur survenu lors de l'ajout d'image.";
                    echo "<script>window.location.href = '../../Site_web_admin/Parcelle.php?showModal=2';</script>";
                    exit;
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }     

?>