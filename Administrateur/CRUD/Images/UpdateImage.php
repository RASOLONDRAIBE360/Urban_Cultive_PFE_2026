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
            

            $targetDir = "../../../Upload"; // Dossier où stocker les images
            $fileName = basename($_FILES["file"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            // Vérifier si le fichier est bien une image
            $allowedTypes = ["jpg", "jpeg", "png", "gif", "webp"];
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['erreurInsert'] = "Seuls les fichiers JPG, JPEG, PNG, GIF et WEBP sont autorisés.";
                exit();
            }

            // Déplacer le fichier uploadé
            if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFilePath)) {
                // Insérer le chemin dans la base de données
                $sql = "INSERT INTO info_parc (chemin_image) VALUES (:targetFilePath)";
                $stmt = $mysqlClient->prepare($sql);
                $stmt->execute([':targetFilePath' => $targetFilePath]);

                $_SESSION['successInsert'] = "Image ajouté avec succès !";
            } else {
                $_SESSION['erreurInsert'] = "Erreur lors de l'upload.";
            }

    } catch (Exception $exception) {
        die('Erreur : ' . $exception->getMessage());
    }     

?>