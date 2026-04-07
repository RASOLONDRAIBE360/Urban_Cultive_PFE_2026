<?php

session_start();
require_once (__DIR__.'/../../../../config/MySQL.php');


$id_res = $_POST["id_res"];

        try{

            $MysqlClient = new PDO(
                sprintf("mysql:host=%s;dbname=%s;port=%s;charset=utf8", MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                MYSQL_USER,
                MYSQL_PASSWORD
            );

            $MysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sqlRequest = "UPDATE reservation_parc 
                        INNER JOIN info_parc 
                        ON reservation_parc.Id_parc = info_parc.Id_parc
                        SET Status_res = 'attente', Status_parc = 'dispo'
                        WHERE reservation_parc.Id_res = :id_res";

            $pdoStatement = $MysqlClient->prepare($sqlRequest);

            $pdoStatement->execute([
                ':id_res'=>$id_res,
            ]);

            $sqlRequestEmail = "SELECT Email 
            FROM users
            INNER JOIN reservation_parc 
            ON users.User_id = reservation_parc.User_id
            WHERE reservation_parc.Id_res = :id_res";

            $emailPrepare = $MysqlClient->prepare($sqlRequestEmail);

            $emailPrepare->execute([
                'id_res' => $id_res,
            ]);

            $Utilisateurs = $emailPrepare->fetchAll(PDO::FETCH_ASSOC);
        
            $_SESSION['Utilisateurs'] = $Utilisateurs;

            if ($pdoStatement->rowCount() > 0 && $emailPrepare->rowCount() > 0){
                $_SESSION['successValidation'] = "Reservation mise en attente";

                // ENVOI AUTOMATIQUE D'EMAIL D'ATTENTE
                require_once(__DIR__.'/../../../../vendor/PHPMailer/src/PHPMailer.php');
                require_once(__DIR__.'/../../../../vendor/PHPMailer/src/Exception.php');
                require_once(__DIR__.'/../../../../vendor/PHPMailer/src/SMTP.php');
                
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'rstevybryan@gmail.com';
                    $mail->Password = 'bxka xoez zjyk ppfe';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    $mail->setFrom('rstevybryan@gmail.com', 'Urban Cultive Admin');
                    
                    $destinataire = $Utilisateurs[0]['Email'];
                    $mail->addAddress($destinataire);
                    $mail->isHTML(true);
                    $mail->Subject = "Votre réservation est toujours en attente";
                    $mail->Body = "<h2>Demande en cours de traitement</h2>
                                  <p>Bonjour, votre réservation est actuellement en cours d'examen par notre équipe d'administration.</p>
                                  <p>Nous reviendrons vers vous très prochainement avec une réponse définitive.</p>";
                    $mail->send();
                    $_SESSION['successEmail'] = "Email d'attente envoyé avec succès !";
                } catch (Exception $e) {
                    $_SESSION['erreurEmail'] = "Erreur lors de l'envoi automatique : {$mail->ErrorInfo}";
                }

                echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
                exit();
            } else {
                $_SESSION['erreurValidation'] = "Reservation déjà mise en attente ou introuvable";
                echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
                exit();
            }

        }catch(Exception $exception){
            $_SESSION['erreurUpdateValidation'] = "Erreur technique : " . $exception->getMessage();
            echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
            exit();
        }

?>