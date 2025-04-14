<?php

session_start();

require_once (__DIR__.'/../../../Config/MySQL.php');

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
            
            $sqlQuery = "INSERT INTO reservation_parc (User_id, Numero_tel, Id_parc, Duree_res, Date_res) 
            VALUES (:user_id, :tel, :id_parc, :duree_res, :date_debut)";

            $dbprepare = $mysqlClient->prepare($sqlQuery);

            $dbprepare->execute([
                ':user_id' => $user_id,
                ':tel' => $tel,
                ':id_parc' => $id_parc,
                ':duree_res'=>$duree_res,
                ':date_debut' => $date_debut,
            ]);
            
            $sqlRequest = "UPDATE reservation_parc
                        SET Date_fin = DATE_ADD(Date_res, INTERVAL Duree_res MONTH);"; 

            $pdoStatement = $mysqlClient->prepare($sqlRequest);

            $pdoStatement->execute();

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
                echo "<script> alert('Demande de reservation envoyee.'); window.location.href = '../../GestionReservation/FormulaireReservation.php'</script>";
            } else {
                echo "<script> alert('Erreur survenu lors de l'envoie de la demande.'); window.location.href = '../../GestionReservation/FormulaireReservation.php';</script>";
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }
        

?>