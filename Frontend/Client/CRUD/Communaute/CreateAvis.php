<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

session_start();

$id_parc = $_POST['id_parc'] ?? null;
$avis = $_POST['avis'] ?? null;
$user_id = $_SESSION['user_id_user'] ?? null;


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sqlQuery = "INSERT INTO avis (User_id, Id_parc, Avis) 
                        VALUES (:user_id, :id_parc, :avis)";

                $dbprepare = $mysqlClient->prepare($sqlQuery);
                $dbprepare->execute([
                    ':user_id' => $user_id,
                    ':id_parc' => $id_parc,
                    ':avis' => $avis,
                ]);

            if($dbprepare->rowCount() > 0) {
                $_SESSION['successPublication'] = "Avis publié !";
                echo '<script>window.location.href = "../../Site_web_user/Communaute.php";</script>';
            } else {
                $_SESSION['erreurPublication'] = "Erreur survenu lors de la publication !";
                echo '<script>window.location.href = "../../Site_web_user/Communaute.php";</script>';
            }

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }

?>