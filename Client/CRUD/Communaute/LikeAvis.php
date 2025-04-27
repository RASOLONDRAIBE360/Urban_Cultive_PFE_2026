<?php

session_start();
require_once (__DIR__.'/../../../Config/MySQL.php');

$id_parc = $_POST['id_parc'];
$user_id = $_SESSION['user_id_user'];
$type_action = $_POST['type_action'];

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlRequestVerifAvis = "SELECT COUNT(*) 
                            FROM like_avis
                            WHERE Type_action IN ('like', 'dislike')
                            AND User_id = :user_id
                            AND Id_parc = :id_parc";

            $dbprepare = $mysqlClient->prepare($sqlRequestVerifAvis);

            $dbprepare->execute([
                ':user_id' => $user_id,
                ':id_parc' => $id_parc,
            ]);
            
            $countAvis = $dbprepare->fetchColumn();

            if($countAvis == 0){

                if($type_action == 'like'){

                    $sqlRequestAjoutAvis = "INSERT INTO like_avis (User_id, Id_parc, Type_action)
                                VALUES(:user_id, :id_parc, 'like')";
                
                    $pdoStatement = $mysqlClient->prepare($sqlRequestAjoutAvis);

                    $pdoStatement->execute([
                        ':user_id' => $user_id,
                        ':id_parc' => $id_parc,
                    ]);

                } else{

                    $sqlRequestAjoutAvis = "INSERT INTO like_avis (User_id, Id_parc, Type_action)
                                VALUES(:user_id, :id_parc, 'dislike')";
                
                    $pdoStatement = $mysqlClient->prepare($sqlRequestAjoutAvis);

                    $pdoStatement->execute([
                        ':user_id' => $user_id,
                        ':id_parc' => $id_parc,
                    ]);

                }

            } else {

                if($type_action == 'like'){

                    $sqlRequestDeleteAvis = "DELETE FROM like_avis
                                WHERE Type_action = 'like'
                                AND User_id = :user_id
                                AND Id_parc = :id_parc";
                            
                    $deleteprepare = $mysqlClient->prepare($sqlRequestDeleteAvis);

                    $deleteprepare->execute([
                        ':user_id' => $user_id,
                        ':id_parc' => $id_parc,
                    ]);

                } else {

                    $sqlRequestDeleteAvis = "DELETE FROM like_avis
                                WHERE Type_action = 'dislike'
                                AND User_id = :user_id
                                AND Id_parc = :id_parc";
                            
                    $deleteprepare = $mysqlClient->prepare($sqlRequestDeleteAvis);

                    $deleteprepare->execute([
                        ':user_id' => $user_id,
                        ':id_parc' => $id_parc,
                    ]);

                }
            } 

            if (isset($pdoStatement) && $pdoStatement->rowCount() > 0 && $type_action == 'like') {
                $_SESSION['NumberLike'] += 1;
                header('Location: ../../Site_web_user/Avis.php');
                exit;
            } else if (isset($deleteprepare) && $deleteprepare->rowCount() > 0 && $type_action == 'like') {
                $_SESSION['NumberLike'] -= 1;
                header('Location: ../../Site_web_user/Avis.php');
                exit;
            } else if(isset($pdoStatement) && $pdoStatement->rowCount() > 0 && $type_action == 'dislike'){
                $_SESSION['NumberDislike'] += 1;
                header('Location: ../../Site_web_user/Avis.php');
                exit;
            } else if (isset($deleteprepare) && $deleteprepare->rowCount() > 0 && $type_action == 'dislike') {
                $_SESSION['NumberDislike'] -= 1;
                header('Location: ../../Site_web_user/Avis.php');
                exit;
            } else {
                header('Location: ../../Site_web_user/Avis.php');
                exit;
            }

            } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
            }

?>