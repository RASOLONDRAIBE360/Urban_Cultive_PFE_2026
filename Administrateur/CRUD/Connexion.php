<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("L\'email saisie n\'est pas valide"); window.location.href = "../Login/Formulaire_inscription.php";</script>';
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT Role FROM users WHERE Nom = :nom AND Prenom = :prenom AND Email = :email AND Mot_de_Passe = :password";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':nom'=>$nom,
                ':prenom'=>$prenom,
                ':email'=>$email,
                ':password'=>$password,
            ]);
                
            $role = $dbprepare->fetchColumn();

            if ($role == 'admin') {
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
                echo "<script> alert('Connexion réussie. Bienvenue, {$nom} {$prenom} !'); window.location.href = '../Site_web_admin/Index.php';</script>";
            } else {
                echo "<script> alert('Page reserver uniquement aux administrateurs.'); window.location.href = '../../Client/Login/Formulaire_connexion.php';</script>";
            }

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        } 

?>