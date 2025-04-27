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


/* Vérification de la soumission */
$destinataires = $_SESSION['EmailWarn'] ?? null;

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

        // 1. Initialisation de PHPMailer une seule fois
        $mail = new PHPMailer(true);

        // 2. Configuration SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'rstevybryan@gmail.com';
        $mail->Password = 'wjwc klmy yznj nxus';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // 3. Configuration email
        $mail->CharSet = 'UTF-8';
        $mail->setFrom('rstevybryan@gmail.com', 'Admin');
        $mail->isHTML(true);
        $mail->Subject = 'Rappel : Pensez à renouveler votre réservation de parcelle';
        $mail->Body = "<p><strong>Bonjour,</strong></p>
                    <p>Nous souhaitons vous rappeler que la date d’expiration de votre réservation de parcelle approche.</p>
                    <p>Pour éviter toute annulation automatique, nous vous invitons à renouveler votre réservation dès que possible.</p>
                    <p>Merci de votre attention.</p>
                    <p>Cordialement,</p>
                    <p>L'équipe d'administration</p>";
        $mail->AltBody = "Bonjour,\nLa date d’expiration de votre réservation approche.
        \nMerci de renouveler rapidement votre réservation.
        \nCordialement,\nL’équipe d’administration.";

        $emailDestinataires = array_unique(array_column($destinataires, 'Email')); // Supprime les doublons

        foreach($emailDestinataires as $email){
            if (!empty($email)){
                $mail->addAddress($email); // Correction ici
            }
        }        
        
        // 5. Envoi unique après avoir ajouté tous les destinataires
        if (count($mail->getAllRecipientAddresses()) > 0 && $_SESSION['Status_envoie_warning'] == 0) {
            $mail->send();
            $sqlRequestUpdateStatusEnvoie = "UPDATE reservation_parc
                                    SET Status_envoie = 1
                                    WHERE Id_res = :id_res";
            
            $updatestatusenvoieprepare = $mysqlClient->prepare($sqlRequestUpdateStatusEnvoie);

            $updatestatusenvoieprepare->execute([
                ':id_res' => $_SESSION['Id_res_warn'],
            ]);

            $_SESSION['successEmail'] = "Email envoyé avec succès !";
        } else {
            header('Location: ../Site_web_admin/Reservation.php');
            exit;
        }

        header('Location: ../Site_web_admin/Reservation.php');
        exit;

    } catch (Exception $e) {

        $_SESSION['erreurEmail'] = "Erreur SMTP : {$mail->ErrorInfo}";
        header('Location: ../Site_web_admin/Reservation.php');
        exit;

    }
