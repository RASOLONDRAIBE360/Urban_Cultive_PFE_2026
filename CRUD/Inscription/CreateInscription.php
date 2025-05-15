<?php
session_start();

require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$num_tel = $_POST['num_tel'] ?? null;
$date = $_POST['date'] ?? null;
$email = $_POST['email'] ?? null;
$motDePasse = $_POST['motDePasse'] ?? null;
$conf_password = $_POST['conf_password'] ?? null;

if (strlen($motDePasse) < 8){
    
    $_SESSION['erreurPwd1'] = "Le mot de passe doit contenir au moins 8 caractères.";
    header('Location: ../../Login/FormulaireInscription.php');
    exit();
}

if($motDePasse != $conf_password){

    $_SESSION['erreurPwd2'] = "Les mots de passe ne correspondent pas.";
    header('Location: ../../Login/FormulaireInscription.php');
    exit();
    
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erreurEmail'] = "Adresse e-mail invalide!!!";
    header('Location: ../../Login/FormulaireInscription.php');
    exit();
}else{

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlRequest = "SELECT COUNT(*) FROM users WHERE Email = :email";

            $stmt = $mysqlClient->prepare($sqlRequest);
            
            $stmt->execute([
                ':email' => $email,
            ]);
                
            $count = $stmt->fetchColumn(); // Récupère la valeur de COUNT(*)

                if($count > 0 ){

                    $_SESSION['erreurEmail'] = "Cet utilisateur existe déjà.";
                    echo '<script>window.location.href = "../../Login/FormulaireInscription.php";</script>';

                } else {
                    
                    $sqlQuery = "INSERT INTO users (Nom, Prenom, Num_tel, Date_Naissance, Email, Mot_de_Passe) VALUES (:nom, :prenom, :num_tel, :date, :email, :motDePasse)";

                    $dbprepare = $mysqlClient->prepare($sqlQuery);

                    $dbprepare->execute([
                        ':nom' => $nom,
                        ':prenom' => $prenom,
                        ':num_tel' => $num_tel,
                        ':date' => $date,
                        ':email' => $email,
                        ':motDePasse' => $motDePasse,
                    ]);

                        if ($dbprepare->rowCount() > 0) {
                            $_SESSION['success'] = "Inscription réussie !";
                            echo "<script>window.location.href = '../../Login/FormulaireInscription.php';</script>";
                        } else {
                            echo "<script> alert('Erreur lors de l\'inscription.'); window.location.href = '../../Login/FormulaireInscription.php';</script>";
                        }

                }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }   
}  
?>