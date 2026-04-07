<?php

session_start();

require_once (__DIR__.'/../../../../config/MySQL.php');

$user_id = $_POST['user_id'] ?? null;
$tel = $_POST['tel'] ?? null;
$id_parc = $_POST['id_parc'] ?? null;
$duree_res = $_POST['duree'] ?? null;
$date_debut = $_POST['date_debut'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlVerifyReservation = "SELECT * 
                        FROM reservation_parc 
                        WHERE User_id = :user_id 
                        AND Id_parc = :id_parc 
                        AND Status_res = 'attente'";

            $dbVerifyReservation = $mysqlClient->prepare($sqlVerifyReservation);

            $dbVerifyReservation->execute([
                ':user_id' => $user_id,
                ':id_parc' => $id_parc
            ]);

            if($dbVerifyReservation->rowCount() > 0) {
                $_SESSION['erreurReservation'] = "Vous avez déjà une réservation en attente pour cette parcelle.";
                echo "<script>window.location.href = '../../gestionReservation/FormulaireReservation.php';</script>";
                exit();
            } else {
                
                $currentDate = date("Y-m-d");

                if ($date_debut < $currentDate) {
                    $_SESSION['erreurReservation'] = "La date de début ne peut pas être dans le passé.";
                    header('Location: ../../gestionReservation/FormulaireReservation.php');
                    exit;
                } else {

                    $sqlQuery = "INSERT INTO reservation_parc (User_id, Id_parc, Duree_res, Date_res) 
                    VALUES (:user_id, :id_parc, :duree_res, :date_debut)";

                    $dbprepare = $mysqlClient->prepare($sqlQuery);

                    $dbprepare->execute([
                        ':user_id' => $user_id,
                        ':id_parc' => $id_parc,
                        ':duree_res'=>$duree_res,
                        ':date_debut' => $date_debut,
                    ]);
                    
                    $sqlRequest = "UPDATE reservation_parc
                                SET Date_fin = DATE_ADD(Date_res, INTERVAL Duree_res MONTH);"; 

                    $pdoStatement = $mysqlClient->prepare($sqlRequest);

                    $pdoStatement->execute();

                    $sqlRequestUpdateDateLimite = "UPDATE reservation_parc
                                    SET Date_limite = DATE_SUB(Date_fin, INTERVAL 3 DAY)
                                    WHERE User_id = :user_id
                                    AND Id_parc = :id_parc";

                    $UpdatePrepare = $mysqlClient->prepare($sqlRequestUpdateDateLimite);

                    $UpdatePrepare->execute([
                        ':user_id'=> $user_id,
                        ':id_parc'=> $id_parc,
                    ]);

                    // Récupérer `Id_res` avec une requête SELECT
                    $sqlSelect = "SELECT Id_res 
                            FROM reservation_parc 
                            WHERE User_id = :user_id 
                            AND Id_parc = :id_parc 
                            ORDER BY Id_res DESC LIMIT 1";

                    $selectPrepare = $mysqlClient->prepare($sqlSelect);

                    $selectPrepare->execute([
                        ':user_id' => $user_id,
                        ':id_parc' => $id_parc
                    ]);
                    
                    $id_res = $selectPrepare->fetchColumn();
                    $_SESSION['id_res'] = $id_res;

                    // Mettre à jour `info_parc` avec `Id_res`
                    $sqlUpdate = "UPDATE info_parc 
                                SET Id_res = :id_res 
                                WHERE Id_parc = :id_parc";

                    $updatePrepare = $mysqlClient->prepare($sqlUpdate);

                    $updatePrepare->execute([
                        ':id_res' => $id_res,
                        ':id_parc' => $id_parc
                    ]);


                    if ($dbprepare->rowCount() > 0 && $pdoStatement->rowCount() > 0 && $selectPrepare->rowCount() > 0 && $updatePrepare->rowCount() > 0) {
                        $_SESSION['successReservation'] = "Votre demande de réservation a été envoyée avec succès.";
                        echo "<script>window.location.href = '../../gestionReservation/FormulaireReservation.php';</script>";
                    } else {
                        $_SESSION['erreurReservation'] = "Une erreur s'est produite lors de l'envoi de votre demande de réservation.";
                        echo "<script>window.location.href = '../../gestionReservation/FormulaireReservation.php';</script>";
                    }
                }
            }

        } catch (Exception $exception) {
            $_SESSION['erreurReservation'] = "Erreur technique : " . $exception->getMessage();
            echo "<script>window.location.href = '../../gestionReservation/FormulaireReservation.php';</script>";
            exit();
        }
        

?>