<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$email = $_POST['email'] ?? null;
$tel = $_POST['numerotel'] ?? null;
$tailleparc = $_POST['tailleparc'] ?? null;
$date = $_POST['date'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("L\'email saisie n\'est pas valide"); window.location.href = "../Reservation.php";</script>';
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "INSERT INTO reservation_parc (Nom, Prenom, Email, Numero_tel, Taille_parc, Date_res) VALUES (:nom, :prenom, :email, :tel, :tailleparc, :date)";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

                    $dbprepare->execute([
                        ':nom' => $nom,
                        ':prenom' => $prenom,
                        ':email' => $email,
                        ':tel' => $tel,
                        ':tailleparc' => $tailleparc,
                        ':date' => $date,
                    ]);

                        if ($dbprepare->rowCount() > 0) {
                            echo "<script> alert('Reservation effectué avec succès.');</script>";
                        } else {
                            echo "<script> alert('Erreur lors de la réservation.'); window.location.href = '../Reservation.php';</script>";
                        }
            }

            $sqlRequest = "SELECT INTO reservation_parc ("
        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        

?>