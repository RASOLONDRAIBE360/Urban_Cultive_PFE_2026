<?php

session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');


$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$date = $_POST['date'];
$email = $_POST['email'];
$id = $_POST['id'];

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo '<script>alert("Verifier le format de l\'email saisie."); window.location.href="../Site_web_admin/Index.php";</script>';
} else {

        try{

            $MysqlClient = new PDO(
                sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                MYSQL_USER,
                MYSQL_PASSWORD
            );

            $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlRequest = "UPDATE users SET Nom = :nom, Prenom = :prenom, Date_Naissance = :date, Email = :email WHERE User_id = :id";
            $pdoStatement = $MysqlClient->prepare($sqlRequest);
            $pdoStatement->execute([
                ':nom'=>$nom,
                ':prenom'=>$prenom,
                ':date'=>$date,
                ':email'=>$email,
                ':id' => $id,
            ]);
             
            if ($pdoStatement->rowCount() > 0){
                $_SESSION['successUpdate'] = "Mise à jour réussi";
                
                $sqlRequest = "SELECT * FROM users WHERE User_id = :id";

                $pdoStatement = $MysqlClient->prepare($sqlRequest);

                $pdoStatement->execute([
                    ':id' => $id,
                ]);

                $_SESSION['UDatas'] = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
                $listeUtilisateurs = $_SESSION['UDatas'];

                foreach($listeUtilisateurs as $utilisateur){
                    if($utilisateur['Role'] == 'admin'){
                        $_SESSION['user_id'] = $utilisateur['User_id'];
                        $_SESSION['nom_admin'] = $utilisateur['Nom'];
                        $_SESSION['prenom_admin'] = $utilisateur['Prenom'];
                        $_SESSION['email_admin'] = $utilisateur['Email'];
                        $_SESSION['role_admin'] = $utilisateur['Role'];
                    } else {
                        $_SESSION['user_id_user'] = $utilisateur['User_id'];
                        $_SESSION['nom_user'] = $utilisateur['Nom'];
                        $_SESSION['prenom_user'] = $utilisateur['Prenom'];
                        $_SESSION['email_user'] = $utilisateur['Email'];
                        $_SESSION['role_user'] = $utilisateur['Role'];
                    }
                }
                echo '<script>window.location.href="../../Site_web_admin/Accueil.php?showModal=1";</script>';
            } else {
                $_SESSION['erreurUpdate'] = "Aucune modification apportée.";
                echo '<script>window.location.href="../../Site_web_admin/Accueil.php?showModal=1";</script>';
            }

        }catch(Exception $exception){
            die('Erreur :'. $exception->getMessage());
        }
}
?>