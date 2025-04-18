<?php

session_start();



/*Récupération de l'adresse email du destinataire qui est stocké dans la variable de session 'mail'*/
/*Importation des bibliothéques qui seront utilisés pour assurer l'envoie d'email de manière plus facile*/
use PHPMailer\PHPMailer\PHPMailer;//Permet l'utilisation de la classe PHPMailer pour assurer la création et l'envoie d'email
use PHPMailer\PHPMailer\SMTP;//Pour l'activation du protocole SMTP, qui est utilisé pour l'envoi des emails
use PHPMailer\PHPMailer\Exception;//Pour la gestion des erreurs qui peuvent survenir lors de l'envoie

require_once(__DIR__.'/../../PHPMailer/src/PHPMailer.php');
require_once(__DIR__.'/../../PHPMailer/src/Exception.php');
require_once(__DIR__.'/../../PHPMailer/src/SMTP.php');


// Vérification de la soumission
    
    $destinataires = $_SESSION['mail']; // Récupération de l'email du destinataire depuis la session

    foreach ($destinataires as $destinataire) {
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
        $mail->addAddress($destinataire['Email']); // ➜ 1 seul destinataire
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de la réservation validée';
        $mail->Body = "<p><strong>Bonjour,</strong></p>
                       <p>Votre réservation a été <b>validée</b> avec succès. Merci pour votre confiance !</p>
                       <p>Cordialement,</p><p>L'équipe d'administration</p>";
        $mail->AltBody = "Bonjour,\nVotre réservation a été validée avec succès.";
    
        // 4. Envoi
        try {

            $mail->send();
            $_SESSION['successEmail'] = "Email envoyé avec succès !";
            echo '<script>window.location.href = "../Site_web_admin/Reservation.php";</script>';
    
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi à {$destinataire['Email']} : {$mail->ErrorInfo}";
        }
    }
?>
