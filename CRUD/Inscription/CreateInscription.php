<?php
session_start();

require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$date = $_POST['date'] ?? null;
$email = $_POST['email'] ?? null;
$motDePasse = $_POST['motDePasse'] ?? null;
$conf_password = $_POST['conf_password'] ?? null;

if (strlen($motDePasse) < 8){
    
    $_SESSION['erreurPwd1'] = "Le mot de passe doit contenir au moins 8 caractères.";
    echo '<script>window.location.href = "../../Login/FormulaireInscription.php";</script>'; 
    exit();
}

if($motDePasse != $conf_password){

    $_SESSION['erreurPwd2'] = "Les mots de passe ne correspondent pas.";
    echo '<script>window.location.href = "../../Login/FormulaireInscription.php";</script>';
    exit();
    
}
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
                    
                    $sqlQuery = "INSERT INTO users (Nom, Prenom, Date_Naissance, Email, Mot_de_Passe) VALUES (:nom, :prenom, :date, :email, :motDePasse)";

                    $dbprepare = $mysqlClient->prepare($sqlQuery);

                    $dbprepare->execute([
                        ':nom' => $nom,
                        ':prenom' => $prenom,
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

?>