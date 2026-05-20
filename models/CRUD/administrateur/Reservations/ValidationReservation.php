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
                        SET Status_res = 'valide', Status_parc = 'occupe'
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
                $_SESSION['successValidation'] = "Reservation validé";

                // ENVOI AUTOMATIQUE D'EMAIL DE VALIDATION
                require_once(__DIR__.'/../../../../vendor/PHPMailer/src/PHPMailer.php');
                require_once(__DIR__.'/../../../../vendor/PHPMailer/src/Exception.php');
                require_once(__DIR__.'/../../../../vendor/PHPMailer/src/SMTP.php');
                
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'rstevybryan@gmail.com';
                    $mail->Password = 'bvkp rsdw lyru kkzf';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;
                    $mail->CharSet = 'UTF-8';
                    $mail->setFrom('rstevybryan@gmail.com', 'Urban Cultive Admin');
                    
                    $destinataire = $Utilisateurs[0]['Email'];
                    $mail->addAddress($destinataire);
                    $mail->isHTML(true);
                    $mail->Subject = "Confirmation de votre réservation";
                    $mail->Body = "<h2>Votre réservation a été validée !</h2>
                                  <p>Bonjour, nous avons le plaisir de vous informer que votre réservation a été acceptée et validée par l'administrateur.</p>
                                  <p>Vous pouvez maintenant profiter de votre parcelle.</p>";
                    $mail->send();
                    $_SESSION['successEmail'] = "Email envoyé automatiquement avec succès !";
                } catch (Exception $e) {
                    $_SESSION['erreurEmail'] = "Erreur lors de l'envoi automatique : {$mail->ErrorInfo}";
                }

                echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
                exit();
            } else {
                $_SESSION['erreurValidation'] = "Reservation déjà validé ou introuvable";
                echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
                exit();
            }
        
        }catch(Exception $exception){
            $_SESSION['erreurUpdateValidation'] = "Erreur technique : " . $exception->getMessage();
            echo '<script>window.location.href="../../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
            exit();
        }

?>