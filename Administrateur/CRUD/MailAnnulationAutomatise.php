<?php
session_start();

require_once(__DIR__.'/../../Config/MySQL.php');

/*Récupération de l'adresse email du destinataire qui est stocké dans la variable de session 'mail'*/
/*Importation des bibliothéques qui seront utilisés pour assurer l'envoie d'email de manière plus facile*/
use PHPMailer\PHPMailer\PHPMailer;//Permet l'utilisation de la classe PHPMailer pour assurer la création et l'envoie d'email
use PHPMailer\PHPMailer\SMTP;//Pour l'activation du protocole SMTP, qui est utilisé pour l'envoi des emails
use PHPMailer\PHPMailer\Exception;//Pour la gestion des erreurs qui peuvent survenir lors de l'envoie

require_once(__DIR__.'/../../PHPMailer/src/PHPMailer.php');
require_once(__DIR__.'/../../PHPMailer/src/Exception.php');
require_once(__DIR__.'/../../PHPMailer/src/SMTP.php');


// Vérification de la soumission
    
    $destinataires = $_SESSION['email']; // Récupération de l'email du destinataire depuis la session
    
    if (empty($destinataires)) {
        die("Erreur : Aucun email trouvé pour l'envoi.");
    }

    try {
        $mysqlClient = new PDO(
            sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                    MYSQL_USER,
                    MYSQL_PASSWORD
                        );

        $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $mail = new PHPMailer(true); // 1. Nouveau mail à chaque tour
    
        // 2. Config SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rstevybryan@gmail.com';
        $mail->Password = 'wjwc klmy yznj nxus';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // 3. Config Email
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('rstevybryan@gmail.com', 'Admin');
        $mail->isHTML(true);
        $mail->Subject = 'Annulation automatique de votre réservation';
        $mail->Body = "<p><strong>Bonjour,</strong></p>
                    <p>Nous vous informons que votre réservation de parcelle a été <b>automatiquement annulée</b> en raison de l'atteinte de la date limite d'expiration.</p>
                    <p>Si vous souhaitez renouveler votre réservation, nous vous invitons à procéder à une nouvelle demande dans les plus brefs délais.</p>
                    <p>Merci pour votre compréhension.</p>
                    <p>Cordialement,</p>
                    <p>L'équipe d'administration</p>";
        $mail->AltBody = "Bonjour,\nVotre réservation de parcelle a été automatiquement annulée en raison de la date limite dépassée.
                \nVous pouvez effectuer une nouvelle demande si vous souhaitez réserver à nouveau.
                \nMerci pour votre compréhension.";

        $emailDestinataires = array_unique(array_column($destinataires, 'Email')); // Supprime les doublons

        foreach($emailDestinataires as $destinataire){

            if (!empty($destinataire['Email'])){
                $mail->addAddress($destinataire['Email']);
            }
        }

            // 4. Envoi

        if (count($mail->getAllRecipientAddresses()) > 0 && $_SESSION['Status_envoie'] == 0) {
            $mail->send();
            $sqlRequestUpdateStatusEnvoie = "UPDATE FROM reservation_parc
                                    SET Status_envoie = 1
                                    WHERE Id_res = :id_res";
            
            $updatestatusenvoieprepare = $mysqlClient->prepare($sqlRequestUpdateStatusEnvoie);

            $updatestatusenvoieprepare->execute([
                ':id_res' => $_SESSION['Id_res'],
            ]);

            $_SESSION['successEmail'] = "Email envoyé avec succès !";
        } else {
            $_SESSION['erreurEmail'] = "Aucun destinataire valide trouvé.";
        }

        header('Location: ../Site_web_admin/Reservation.php');
        exit;

    } catch (Exception $e) {
        echo "Erreur lors de l'envoi à {$destinataire['Email']} : {$mail->ErrorInfo}";
    }
?>
