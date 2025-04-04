<?php

session_start();

require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$email = $_POST['email'] ?? null;
$motDePasse = $_POST['motDePasse'] ?? null;

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo '<script>alert("L\'email saisie n\'est pas valide"); window.location.href = "../Login/Formulaire_connexion.php";</script>';
} else if(strlen($motDePasse) < 5){
    echo '<script>alert("Mot de passe trop court"); window.location.href = "../Login/Formulaire_connexion.php";</script>';
} else {

    try {
        $mysqlClient = new PDO(
            sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
            MYSQL_USER,
            MYSQL_PASSWORD
        );

        $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT COUNT(*) FROM users WHERE Nom = :nom AND Prenom = :prenom AND Email = :email";

        $stmt = $mysqlClient->prepare($sql);
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
        ]);

        $count = $stmt->fetchColumn(); // Récupère la valeur de COUNT(*)
        $sql1 = "SELECT COUNT(*) FROM users WHERE Nom = :nom AND Prenom = :prenom AND Mot_de_Passe = :motDePasse"; 

        $verif_pwd = $mysqlClient->prepare($sql1);

        $verif_pwd->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':motDePasse' => $motDePasse,
        ]);

        $count1 = $verif_pwd->fetchColumn();

        if($count == 0 ){
            echo '<script>alert("Compte invalide. Veuillez vous inscrire."); window.location.href = "../Login/Formulaire_inscription.php";</script>';

        } else if($count1 == 0) {
            echo '<script>alert("Mot de passe incorrecte."); window.location.href = "../Login/Formulaire_connexion.php";</script>';

        } else {
            // Stocker les informations de l'utilisateur dans la session
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
            $_SESSION['motDePasse'] = $motDePasse;
            $_SESSION['email'] = $email;
            //header('Location: ../Accueil.php');
            echo "<script>window.location.href = '../Accueil.php';</script>";
            exit();
        }

    } catch (Exception $exception) {
        die('Erreur : ' . $exception->getMessage());
    }

}
?>