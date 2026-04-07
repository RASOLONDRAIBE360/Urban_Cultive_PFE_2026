<?php
require_once (__DIR__.'/../../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$date = $_POST['date'] ?? null;
$email = $_POST['email'] ?? null;
$motDePasse = $_POST['motDePasse'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erreurEmail'] = "Le format Email est invalide.";
    echo '<script>window.location.href = "../../Login/Formulaire_inscription.php";</script>';
    exit();
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlRequest = "INSERT INTO users (Nom, Prenom, Date_Naissance, Email, Mot_de_Passe) VALUES (:nom, :prenom, :date, :email, :motDePasse)";
            $pdoStatement = $mysqlClient->prepare($sqlRequest);
            $pdoStatement->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':date' => $date,
                ':email' => $email,
                ':motDePasse' => $motDePasse,
            ]);

            $pdoStatement->fetchColumn();

            if($pdoStatement->rowCount() > 0){
                echo ('<script>window.location.href = "../../Login/Formulaire_connexion.php";</script>');
            } else {
                $_SESSION['erreurEmail'] = "L'utilisateur existe déjà.";
                echo '<script>window.location.href = "../../Login/Formulaire_inscription.php";</script>';
            }

            } catch (Exception $exception) {
            $_SESSION['erreurEmail'] = "Erreur technique : " . $exception->getMessage();
            echo '<script>window.location.href = "../../Login/Formulaire_inscription.php";</script>';
            exit();
            }
        }

?>