<?php
require_once (__DIR__.'/../../Config/MySQL.php');

session_start();

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("L\'email saisie n\'est pas valide"); window.location.href = "../Login/Connexion.html";</script>';
} else {

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * FROM users WHERE Email = :email AND Mot_de_Passe = :password";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':email'=>$email,
                ':password'=>$password,
            ]);
                
            $utilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            foreach($utilisateurs as $utilisateur){
                $nom = $utilisateur['Nom'];
                $prenom = $utilisateur['Prenom'];
                $role = $utilisateur['Role'];
            }
            
            if ($role == 'admin') {
                // Stocker les informations de l'utilisateur dans la session
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                echo '<script> window.location.href = "../Site_web_admin/Accueil.php";</script>';

            } else {
                echo "<script> alert('Page reserver uniquement aux administrateurs.'); window.location.href = '../../Client/Login/Formulaire_connexion.php';</script>";
            }

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        } 

?>