<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$name = $_POST(['nom']);
$prenom = $_POST(['prenom']);
$age = $_POST(['age']);
$email = $_POST(['email']);
$password = $_POST(['password']);

if(empty($name)){
    echo '<script>alert("Veuillez vérifier le champ Nom."); window.location.href =';
} else if(filter_var($email, FILTER_VALIDATE_EMAIL)){
    
}

try{
    $MysqlClient = new PDO(
        sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );

    $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlRequest = "INSERT INTO users (Name, Prenom, Age, Email, Password) VALUES (:name, :prenom, :age, :email)";
    $pdoStatement = $MysqlClient->prepare($sqlRequest);
    $pdoStatement->execute([
        ':name'=>$name,
        ':prenom'=>$prenom,
        ':age'=>$age
    ])
}catch(Exception exception){
    die('Erreur :'.exception->getMessage());
}

?>