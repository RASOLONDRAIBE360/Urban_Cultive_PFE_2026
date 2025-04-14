<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

session_start();

$email = $_POST['email'] ?? null;
$id_parc = $_POST['id_parc'] ?? null;
$avis = $_POST['avis'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("Le format Email est invalide."); window.location.href = "../Communaute.php";</script>';
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($email != $_SESSION['email']) {
                echo '<script>alert("Veuillez entrer l\'email exacte."); window.location.href = "../../Communaute.php";</script>';
                exit();
            } else{
                
                    $sqlQuery = "INSERT INTO avis (User_id, Id_parc, Avis) VALUES (:user_id, :id_parc, :avis)";

                $dbprepare = $mysqlClient->prepare($sqlQuery);
                $dbprepare->execute([
                    ':user_id' => $user_id,
                    ':id_parc' => $id_parc,
                    ':avis' => $avis,
                ]);
            }

            if($dbprepare->rowCount() > 0) {
                echo '<script>window.location.href = "../../Communaute.php";</script>';
            } else {
                echo '<script>alert("Erreur survenu lors du partage d\'avis"); window.location.href = "../../Communaute.php";</script>';
            }

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
}

?>