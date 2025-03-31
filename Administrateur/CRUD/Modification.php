<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$date = $_POST['date'];
$email = $_POST['email'];
$password = $_POST['password'];
$role = $_POST['role'];
if(filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo "Verifier le format de l'email saisie.";
} else if(strlen($motDePasse) <= 4) {
    echo '<script>alert("Mot de passe trop court"); window.location.href = "../Site_web_admin/Index.php";</script>';
} else {

        try{
            $MysqlClient = new PDO(
                sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                MYSQL_USER,
                MYSQL_PASSWORD
            );

            $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sqlRequest = "UPDATE users SET Nom = :nom AND Prenom = :prenom AND Date_Naissance = :date AND Email = :email AND Mot_de_Passe = :password AND Role = :role WHERE Email = :email AND Mot_de_Passe = :password";
            $pdoStatement = $MysqlClient->prepare($sqlRequest);
            $pdoStatement->execute([
                ':nom'=>$nom,
                ':prenom'=>$prenom,
                ':date'=>$date,
                ':email'=>$email,
                ':password'=>$password,
                ':role'=>$role,
            ])

            if($pdoStatement->rowCount() > 0){
                echo "Modification(s) enregistrée(s)";
            } else {
                echo "Modification du champ échouée.";
            }

        }catch(Exception exception){
            die('Erreur :'.exception->getMessage());
        }
}
?>