<?php

session_start();
/*Importation des bibliothéques qui seront utilisés pour assurer l'envoie d'email de manière plus facile*/
use PHPMailer\PHPMailer\PHPMailer;//Permet l'utilisation de la classe PHPMailer pour assurer la création et l'envoie d'email
use PHPMailer\PHPMailer\SMTP;//Pour l'activation du protocole SMTP, qui est utilisé pour l'envoi des emails
use PHPMailer\PHPMailer\Exception;//Pour la gestion des erreurs qui peuvent survenir lors de l'envoie

require_once(__DIR__.'/../../PHPMailer/src/PHPMailer.php');
require_once(__DIR__.'/../../PHPMailer/src/Exception.php');
require_once(__DIR__.'/../../PHPMailer/src/SMTP.php');


// Vérification de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mail = new PHPMailer(true);
    
    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'rstevybryan@gmail.com';
    $mail->Password = 'bvkp rsdw lyru kkzf';/*Mot de passe d'application qui permet d'eviter les exigences de sécurité par rapport à la connexion à deux étapes
    lorsque je me sert du service gmail pour l'envoie des emails pour ainsi éviter le déclenchement d'alerte pour le non suivie du procédure de connexion à deux étapes
    En d'autre terme le mot de passe d'application va permettre à des services tiers (comme PHPMailer) d'envoyer des e-mails sans être bloqués par la vérification en deux étapes de Gmail
*/ 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //STARTTLS est un protocole de sécurité utilisé pour chiffrer la connexion entre ton serveur et Gmail. Pour permettre
    //à mon serveur d'envoyer/de communiquer (par envoie d'email) avec mon service Gmail

    $mail->Port = 587;//Le choix de ce port est parce que j'utilise le protocole de sécurité STARTTLS pour assurer la sécurité de connexion entre mon serveur et le service gmail en ligne

    // Récupération des données du formulaire
    $destinataire = htmlspecialchars($_POST['dest']);
    $objet = htmlspecialchars($_POST['objet']);
    $message = htmlspecialchars($_POST['message']);

    // Configuration du mail
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('rstevybryan@gmail.com', 'admin');
    $mail->addAddress($destinataire);
    $mail->Subject = $objet;
    
    // Contenu HTML avec données dynamiques
    $mail->isHTML(true);/*Activation du format html qui prendre en charge la présentation du message envoyé pour un format plus stylé */
    $mail->Body = "<h2>Message reçu via formulaire</h2>
                  <p><strong>Destinataire : </strong>$destinataire</p>
                  <p><strong>Message : </strong>$message</p>";
    /*Definition de la structure de l'email qui sera envoyé au destinataire spécifié */
    $mail->AltBody = "Message texte : $message";/*Dans le cas où le format html n'est pas prise en charge cette deuxième format
    permettra de déclarer la structure du message a envoyé */

    try {

        if($mail->send()){ 
            $_SESSION['successEmail'] = "Email envoyé avec succès !";
            echo '<script>window.location.href = "../../../views/administrateur/Site_web_admin/Reservation.php";</script>';
            exit;
        } else {
            $_SESSION['erreurEmail'] = "Echec de l'envoie d'email";
            echo '<script>window.location.href = "../../../views/administrateur/Site_web_admin/Reservation.php?showModal=1";</script>';
            exit;
        }
        
    } catch (Exception $e) {
        echo "Erreur : {$mail->ErrorInfo}";
    }
}
?>
