<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$email = $_POST['email'] ?? null;
$tel = $_POST['tel'] ?? null;
$nom_parc = $_POST['nom_parc'] ?? null;
$prix_parc = $_POST['prix'] ?? null;
$taille_parc = $_POST['taille'] ?? null;
$duree_res = $_POST['duree'] ?? null;
$date_debut = $_POST['date_debut'] ?? null;

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
            
            $sqlQuery = "INSERT INTO reservation_parc (Nom, Prenom, Email, Numero_tel, Id_parc, Taille_parc, Prix_parc, Date_res, Duree_res) VALUES (:nom, :prenom, :email, :tel, :id_parc, :prix, :taille, :duree, :date_debut)";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

                    $dbprepare->execute([
                        ':nom' => $nom,
                        ':prenom' => $prenom,
                        ':email' => $email,
                        ':tel' => $tel,
                        ':nom_parc'=>$nom_parc,
                        ':prix'=>$prix_parc,
                        ':taille'=>$taille_parc,
                        ':duree'=>$duree_res,
                        ':date_debut' => $date_debut,
                    ]);

                        if ($dbprepare->rowCount() > 0) {
                            echo "<script> alert('Reservation effectué avec succès.');</script>";
                        } else {
                            echo "<script> alert('Erreur lors de la réservation.'); window.location.href = '../Reservation.php';</script>";
                        }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }
    }
        

?>