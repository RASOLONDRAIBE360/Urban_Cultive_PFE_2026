<?php
require_once (__DIR__.'/../../Config/MySQL.php');

session_start();

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlQuery = "SELECT * FROM users WHERE Email = :email";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':email'=>$email,
            ]);
            
            $utilisateurs = $dbprepare->fetchAll(PDO::FETCH_ASSOC);

            foreach($utilisateurs as $utilisateur){
                
                if($utilisateur['Role'] != 'admin'){
                    echo '<script>alert("Cette page est reservé aux administrateurs uniquement."); window.location.href = "../../Client/Login/Formulaire_connexion.php";</script>';
                } else {
                    // Vérifier le mot de passe
                    if($utilisateur['Mot_de_Passe'] != $password){
                            echo '<script>alert("Mot de passe incorrect."); window.location.href = "../Login/FormulaireConnexion.php";</script>';
                        } else {
                            if($utilisateur['Role'] == 'admin'){

                                $nom = $utilisateur['Nom'];
                                $prenom = $utilisateur['Prenom'];
                                $role = $utilisateur['Role'];

                                // Stocker les informations de l'utilisateur dans la session
                                $_SESSION['nom'] = $nom;
                                $_SESSION['prenom'] = $prenom;
                                $_SESSION['email'] = $email;
                                $_SESSION['role'] = $role;

                                echo '<script>window.location.href = "../Site_web_admin/Accueil.php";</script>';
                                exit();
                            }
                    }
                }
            }
            
            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }

?>