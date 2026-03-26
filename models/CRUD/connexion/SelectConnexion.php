<?php
require_once (__DIR__.'/../../../config/MySQL.php');

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

            if($dbprepare->rowCount() > 0){
                
                foreach($utilisateurs as $utilisateur){
        
                    if($utilisateur['Role'] != 'admin'){
                        if($utilisateur['Mot_de_Passe'] != $password){
                            $_SESSION['erreurPwd'] = "Mot de passe incorrect.";
                            echo '<script>window.location.href = "../../../views/login/FormulaireConnexion.php";</script>';

                        } else {

                            $nom = $utilisateur['Nom'];
                            $prenom = $utilisateur['Prenom'];
                            $role = $utilisateur['Role'];
                            $user_id = $utilisateur['User_id'];

                            $_SESSION['user_id_user'] = $user_id;
                            $_SESSION['nom_user'] = $nom;
                            $_SESSION['prenom_user'] = $prenom;
                            $_SESSION['email_user'] = $email;
                            $_SESSION['role_user'] = $role;

                            echo '<script>window.location.href = "../../../views/client/site_web_user/Accueil.php";</script>';
                            exit();

                        }
                    } else {
                        // Vérifier le mot de passe
                        if($utilisateur['Mot_de_Passe'] != $password){
                                $_SESSION['erreurPwd'] = "Mot de passe incorrect.";
                                echo '<script>window.location.href = "../../../views/login/FormulaireConnexion.php";</script>';
                            } else {

                                    $nom = $utilisateur['Nom'];
                                    $prenom = $utilisateur['Prenom'];
                                    $role = $utilisateur['Role'];
                                    $user_id = $utilisateur['User_id'];

                                    // Stocker les informations de l'utilisateur dans la session
                                    $_SESSION['user_id_admin'] = $user_id;
                                    $_SESSION['nom_admin'] = $nom;
                                    $_SESSION['prenom_admin'] = $prenom;
                                    $_SESSION['email_admin'] = $email;
                                    $_SESSION['role_admin'] = $role;

                                    echo '<script>window.location.href = "../../../views/administrateur/Site_web_admin/Accueil.php";</script>';
                                    exit();
                            }
                    }
                }

            } else {
                $_SESSION['erreurEmail'] = "Email introuvable.";
                echo '<script>window.location.href = "../../../views/login/FormulaireConnexion.php";</script>';
            }

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }

?>