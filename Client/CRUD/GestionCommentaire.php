<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$titre = $_POST['titre'] ?? null;
$email = $_POST['email'] ?? null;
$conseil = $_POST['conseil'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("Le format Email est invalide."); window.location.href = "../Commentaire.php";</script>';
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "INSERT INTO commentaire (Titre, Email, Conseil) VALUES (:titre, :email, :conseil)";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $sqlRequest = "SELECT COUNT(*) FROM users WHERE Email = :email";

            $stmt = $mysqlClient->prepare($sqlRequest);
            $stmt->execute([
                ':email' => $email,
            ]);

            $count = $stmt->fetchColumn();

            if($count>0){
                $dbprepare->execute([
                    ':titre' => $titre,
                    ':email' => $email,
                    ':conseil' => $conseil,
                        ]);
    
                    if ($dbprepare->rowCount() > 0) {
                        echo "<script> window.location.href = '../Commentaire.php';</script>";
                    } else {
                        echo "<script> alert('Erreur survenu lors de la publication du commentaire.'); window.location.href = '../Commentaire.php';</script>";
                            }
                
            } else {
                echo '<script>alert("Email non reconnu"); window.location.href="../Commentaire.php";</script>';
            }

            

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        }

?>