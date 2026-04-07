<?php
session_start();

require_once (__DIR__.'/../../../../config/MySQL.php');

$id_parc = $_POST['id_parc'] ?? null;
$user_id = $_SESSION['user_id_user'] ?? null;

        try {
            $mysqlClient = new PDO(
                    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                            MYSQL_USER,
                            MYSQL_PASSWORD
                                );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sqlRequestChoice = "SELECT Status_res, Date_fin
                        FROM reservation_parc 
                        WHERE Id_parc = :id_parc AND User_id = :user_id";  
                        
            $dbRequestChoice = $mysqlClient->prepare($sqlRequestChoice);

            $dbRequestChoice->execute([
                ':id_parc' => $id_parc,
                ':user_id' => $user_id,
            ]);

            $reservations = $dbRequestChoice->fetch(PDO::FETCH_ASSOC);

            $sqlQuery = "DELETE FROM reservation_parc 
                        WHERE Id_parc = :id_parc 
                        AND User_id = :user_id";
            
            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute([
                ':id_parc' => $id_parc,
                ':user_id' => $user_id,
            ]);

            $sqlRequestUpdate = "UPDATE info_parc
                        SET Status_parc ='dispo', Id_res = NULL
                        WHERE Id_parc = :id_parc";

            $dbRequestUpdate = $mysqlClient->prepare($sqlRequestUpdate);

            $dbRequestUpdate->execute([
                ':id_parc' => $id_parc,
            ]);

            if ($dbprepare->rowCount() > 0) {
                    /*Condition pour ne prendre en compte que les blocs de parcelles dont l'utilisateur possède déjà */
                    if($reservations['Status_res'] == 'valide' && $reservations['Date_fin'] == date("Y-m-d")){
                        $_SESSION['successAnnulationRenouv'] = "Renouvellement annulé avec succès.";
                        echo '<script>window.location.href = "../../../../views/client/site_web_user/Parcelle.php";</script>';
                        exit;
                    } else {/*Pour désigner les parcelles qui sont encore en cours de validation par l'admin */
                        $_SESSION['successAnnulation'] = "Réservation annulée avec succès.";
                        echo "<script>window.location.href = '../../../../views/client/site_web_user/Reservation.php';</script>";
                        exit;
                    }
            } else {
                    /*Condition pour ne prendre en compte que les blocs de parcelles dont l'utilisateur possède déjà */
                    if($reservations['Status_res'] == 'valide' && $reservations['Date_fin'] == date("Y-m-d")){
                        $_SESSION['erreurAnnulationRenouv'] = "Erreur survenu lors de l'annulation du renouvellement.";
                        echo "<script>window.location.href = '../../../../views/client/site_web_user/Parcelle.php';</script>";
                        exit;
                    } else {/*Pour désigner les parcelles qui sont encore en cours de validation par l'admin */
                        $_SESSION['erreurAnnulation'] = "Erreur survenu lors de l'annulation de la réservation.";
                        echo "<script>window.location.href = '../../../../views/client/site_web_user/Reservation.php';</script>";
                        exit;
                   }
            }
            
        } catch (Exception $exception) {
            $_SESSION['erreurAnnulation'] = "Erreur technique : " . $exception->getMessage();
            echo "<script>window.location.href = '../../../../views/client/site_web_user/Reservation.php';</script>";
            exit();
        }

?>