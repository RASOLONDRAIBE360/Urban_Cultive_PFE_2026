<?php
// On vérifie si la session n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once (__DIR__.'/../../../config/MySQL.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__.'/../../../vendor/PHPMailer/src/PHPMailer.php');
require_once(__DIR__.'/../../../vendor/PHPMailer/src/Exception.php');
require_once(__DIR__.'/../../../vendor/PHPMailer/src/SMTP.php');

try {
    $mysqlClient = new PDO(
        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
        MYSQL_USER,
        MYSQL_PASSWORD
    );

    $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer toutes les réservations valides qui ont atteint leur date limite ou leur date de fin
    $sqlQuery = "SELECT info_parc.Id_parc, reservation_parc.Id_res, users.Email, 
                        reservation_parc.Date_limite, reservation_parc.Date_fin, reservation_parc.Status_envoie
                 FROM info_parc 
                 INNER JOIN reservation_parc 
                 ON info_parc.Id_parc = reservation_parc.Id_parc 
                 INNER JOIN users
                 ON reservation_parc.User_id = users.User_id
                 WHERE Status_res = 'valide' 
                 AND (Date_limite <= CURDATE() OR Date_fin = CURDATE())";

    $dbprepare = $mysqlClient->prepare($sqlQuery);
    $dbprepare->execute();
    $reservations = $dbprepare->fetchAll(PDO::FETCH_ASSOC);

    if (count($reservations) > 0) {
        // Initialisation de PHPMailer une seule fois pour toutes les opérations
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rstevybryan@gmail.com';
        $mail->Password = 'bvkp rsdw lyru kkzf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('rstevybryan@gmail.com', 'Admin');

        $emailsSent = 0;
        $cancellationsDone = 0;
        $errors = [];

        $today = new DateTime();
        $todayFormatted = $today->format('Y-m-d');

        foreach ($reservations as $res) {
            $email = $res['Email'];
            $id_res = $res['Id_res'];
            $id_parc = $res['Id_parc'];
            $date_limite = $res['Date_limite'];
            $date_fin = $res['Date_fin'];
            $status_envoie = $res['Status_envoie'];

            // 1. Cas d'avertissement : Date limite atteinte ou dépassée, et e-mail non encore envoyé
            if ($todayFormatted >= $date_limite && $status_envoie == 0) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Rappel : Pensez à renouveler votre réservation de parcelle';
                    $mail->Body = "<p><strong>Bonjour,</strong></p>
                                <p>Nous souhaitons vous rappeler que la date d’expiration de votre réservation de parcelle approche.</p>
                                <p>Pour éviter toute annulation automatique, nous vous invitons à renouveler votre réservation dès que possible.</p>
                                <p>Merci de votre attention.</p>
                                <p>Cordialement,</p>
                                <p>L'équipe d'administration</p>";
                    $mail->AltBody = "Bonjour,\nLa date d’expiration de votre réservation approche.\nMerci de renouveler rapidement votre réservation.\nCordialement,\nL’équipe d’administration.";
                    
                    $mail->send();

                    // Mise à jour du statut d'envoi pour cette réservation spécifique dans la base de données
                    $sqlUpdate = "UPDATE reservation_parc SET Status_envoie = 1 WHERE Id_res = :id_res";
                    $updatePrep = $mysqlClient->prepare($sqlUpdate);
                    $updatePrep->execute([':id_res' => $id_res]);

                    $emailsSent++;
                } catch (Exception $e) {
                    $errors[] = "Erreur d'envoi d'avertissement à {$email} : {$mail->ErrorInfo}";
                }
            }
            // 2. Cas d'annulation : Date de fin atteinte et avertissement déjà envoyé (Status_envoie == 1)
            elseif ($date_fin == $todayFormatted && $status_envoie == 1) {
                try {
                    $mail->clearAddresses();
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Annulation automatique de votre réservation';
                    $mail->Body = "<p><strong>Bonjour,</strong></p>
                                <p>Nous vous informons que votre réservation de parcelle a été <b>automatiquement annulée</b> en raison de l'atteinte de la date limite d'expiration.</p>
                                <p>Si vous souhaitez renouveler votre réservation, nous vous invitons à procéder à une nouvelle demande dans les plus brefs délais.</p>
                                <p>Merci pour votre compréhension.</p>
                                <p>Cordialement,</p>
                                <p>L'équipe d'administration</p>";
                    $mail->AltBody = "Bonjour,\nVotre réservation de parcelle a été automatiquement annulée en raison de la date limite dépassée.\nVous pouvez effectuer une nouvelle demande si vous souhaitez réserver à nouveau.\nMerci pour votre compréhension.";
                    
                    $mail->send();

                    // Suppression de la réservation
                    $sqlDelete = "DELETE FROM reservation_parc WHERE Id_res = :id_res";
                    $deletePrep = $mysqlClient->prepare($sqlDelete);
                    $deletePrep->execute([':id_res' => $id_res]);

                    // Libération de la parcelle associée
                    $sqlUpdateParc = "UPDATE info_parc SET Status_parc = 'dispo', Id_res = NULL WHERE Id_parc = :id_parc";
                    $updateParcPrep = $mysqlClient->prepare($sqlUpdateParc);
                    $updateParcPrep->execute([':id_parc' => $id_parc]);

                    $cancellationsDone++;
                } catch (Exception $e) {
                    $errors[] = "Erreur lors de l'annulation pour {$email} : {$mail->ErrorInfo}";
                }
            }
        }

        // Enregistrement des retours dans la session pour affichage à l'utilisateur
        if ($emailsSent > 0 || $cancellationsDone > 0) {
            $_SESSION['successEmail'] = "Traitement automatique terminé : " 
                . ($emailsSent > 0 ? "{$emailsSent} rappel(s) envoyé(s). " : "")
                . ($cancellationsDone > 0 ? "{$cancellationsDone} annulation(s) effectuée(s)." : "");
        }

        if (count($errors) > 0) {
            $_SESSION['erreurEmail'] = implode("<br>", $errors);
        }
    }

} catch (Exception $exception) {
    // Ne pas bloquer toute l'application en cas d'erreur de base de données, mais lever une session d'erreur
    $_SESSION['erreurEmail'] = "Erreur base de données (traitement e-mails) : " . $exception->getMessage();
}