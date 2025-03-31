<?php

    require_once (__DIR__.'/../config/MySQL.php');

    $new_password = $_POST['new_pwd'] ?? null;
    $conf_password = $_POST['conf_pwd'] ?? null;
    $email = $_POST['email'] ?? null;

    try {
        $mysqlClient = new PDO(
            sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
            MYSQL_USER,
            MYSQL_PASSWORD
        );
    
        $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sqlrequest = "UPDATE users SET Mot_de_Passe = :new_password WHERE Email = :email";
        $pdoStatement1 = $mysqlClient->prepare($sqlrequest);
    
    if(
        !isset($_POST['new_pwd']) &&
        empty($new_password) &&
        !isset($_POST['conf_pwd']) &&
        empty($conf_pwd) &&
        !isset($_POST['email']) &&
        empty($email)
        ){

            echo '<script>alert("Vérifier le champ : Nom & Mot de Passe."); window.location.href= "../Login/FormulaireUpdatePassword.php";</script>';

        } else {
            
            $sqlContent = "SELECT COUNT(*) FROM users WHERE Email = :email AND Mot_de_Passe = :new_password";
            $pdoStatement2 = $mysqlClient->prepare($sqlContent);
            $pdoStatement2->execute([
                ':email' => $email,
                ':new_password' => $new_password,
            ]);

            $count = $pdoStatement2->fetchColumn(); // Récupère la valeur de COUNT(*)

            if($count > 0 ){
                echo '<script>alert("Veuillez entrer un nouveau mot de passe différent de l\'ancien."); window.location.href= "../Login/FormulaireUpdatePassword.php";</script>';

            } else {
                $pdoStatement1->execute([
                    ':new_password' => $new_password,
                    ':email' => $email,
                ]);

                echo '<script>alert("Mot de passe modifier avec succès."); window.location.href= "../Login/Formulaire_connexion.php";</script>';

            }
}

    } catch (Exception $exception) {
        die('Erreur : ' . $exception->getMessage());
    }

?>