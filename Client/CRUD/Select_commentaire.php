<?php
require_once (__DIR__.'/../../Config/MySQL.php');

$titre = $_POST['titre'] ?? null;
$nom = $_POST['nom'] ?? null;
$prenom = $_POST['prenom'] ?? null;
$conseil = $_POST['conseil'] ?? null;


        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT users.Email, commentaire.Date, commentaire.Titre, commentaire.Conseil FROM users INNER JOIN commentaire ON users.Email = commentaire.Email";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute();
                
            $listeUtilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);

            if(empty($listeUtilisateurs))
                echo "Aucune commentaire pour l'instant.";

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }
        //} 

?>