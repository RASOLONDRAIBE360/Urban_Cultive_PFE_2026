<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$date = $_POST['date'] ?? null;
$email = $_POST['email'] ?? null;
$motDePasse = $_POST['motDePasse'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("L\'email saisie n\'est pas valide"); window.location.href = "../Login/Formulaire_inscription.php";</script>';
} else if (strlen($motDePasse) <= 4) {
    echo '<script>alert("Mot de passe trop court"); window.location.href = "../Login/Formulaire_inscription.php";</script>';
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            if (
                !filter_var($email, FILTER_VALIDATE_EMAIL)
                ){
                    echo '<script>alert("L\'email saisie n\'est pas valide"); window.location.href = "../Login/Formulaire_inscription.php";</script>';

                } else if (
                        strlen($motDePasse) <= 4
                        ){
            
                        echo '<script>alert("Mot de passe trop court"); window.location.href = "../Login/Formulaire_inscription.php";</script>'; 
                    } else {

            $sqlQuery = "INSERT INTO users (Nom, Prenom, Date_Naissance, Email, Mot_de_Passe) VALUES (:nom, :prenom, :date, :email, :motDePasse)";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $sql = "SELECT COUNT(*) FROM users WHERE Nom = :nom AND Prenom = :prenom AND Email = :email AND Mot_de_Passe = :motDePasse";

            $stmt = $mysqlClient->prepare($sql);
            
            $stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':email' => $email,
                ':motDePasse' => $motDePasse,
                ]);
                
            $count = $stmt->fetchColumn(); // Récupère la valeur de COUNT(*)

                if($count > 0 ){
                    echo '<script> alert("L\'utilisateur existe déjà."); window.location.href = "../Login/Formulaire_inscription.php";</script>';
                } else {
                    $dbprepare->execute([
                        ':nom' => $nom,
                        ':prenom' => $prenom,
                        ':date' => $date,
                        ':email' => $email,
                        ':motDePasse' => $motDePasse,
                    ]);

                        if ($dbprepare->rowCount() > 0) {
                            echo "<script> alert('Inscription reussi.'); window.location.href = '../Login/Formulaire_connexion.php';</script>";
                        } else {
                            echo "<script> alert('Erreur lors de l\'inscription.'); window.location.href = '../Login/Formulaire_inscription.php';</script>";
                        }
                }
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        }        

?>