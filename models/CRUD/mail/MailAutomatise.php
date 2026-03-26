<?php
session_start();

require_once (__DIR__.'/../../../config/MySQL.php');

        try{
            $mysqlClient = new PDO(
                sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                        MYSQL_USER,
                        MYSQL_PASSWORD
                            );

            $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $sqlQuery = "SELECT info_parc.Id_parc, Status_parc, Status_res, Date_fin, Date_res, Date_limite, Status_envoie
                    FROM info_parc 
                    INNER JOIN reservation_parc 
                    ON info_parc.Id_parc = reservation_parc.Id_parc 
                    WHERE Status_res = 'valide' 
                    AND (Date_limite <= CURDATE() OR Date_fin = CURDATE())";

            $dbprepare = $mysqlClient->prepare($sqlQuery);
            
            $dbprepare->execute();

            $MyParcelles = $dbprepare->fetchAll(PDO::FETCH_ASSOC);
            
            if($dbprepare->rowCount() > 0){

                foreach($MyParcelles as $MyParcelle){

                    $today = new DateTime();//Instanciation de la classe DateTime nommé today 
                    //permettant de manipuler non seulement mais aussi l'heure
                    $todayFormatted = $today->format('Y-m-d');

                    if($todayFormatted >= $MyParcelle['Date_limite'] AND $MyParcelle['Status_envoie'] == 0){

                        $sqlRequestEmailWarn = "SELECT Email, Status_envoie, Id_res
                                        FROM users 
                                        INNER JOIN reservation_parc
                                        ON users.User_id = reservation_parc.User_id
                                        WHERE Date_limite <= CURDATE()";
                            
                        $emailwarnprepare = $mysqlClient->prepare($sqlRequestEmailWarn);

                        $emailwarnprepare->execute();

                        $EmailWarn = $emailwarnprepare->fetchAll(PDO::FETCH_ASSOC);

                        foreach($EmailWarn as $emailWarn){
                            $_SESSION['Status_envoie_warning'] = $emailWarn['Status_envoie'];
                            $_SESSION['Id_res_warn'] = $emailWarn['Id_res'];
                        }
                        
                    } else if ($MyParcelle['Date_fin'] == $todayFormatted AND $MyParcelle['Status_envoie'] == 1){

                            $sqlRequestEmail = "SELECT Email, Status_envoie, Id_res
                                        FROM users 
                                        INNER JOIN reservation_parc
                                        ON users.User_id = reservation_parc.User_id
                                        WHERE Date_fin = CURDATE()";
                            
                            $emailprepare = $mysqlClient->prepare($sqlRequestEmail);

                            $emailprepare->execute();

                            $Email = $emailprepare->fetchAll(PDO::FETCH_ASSOC);

                            foreach($Email as $email){
                                $_SESSION['Status_envoie'] = $email['Status_envoie'];
                                $_SESSION['Id_res'] = $email['Id_res'];
                            }

                    } else {

                        $_SESSION['erreurRenewal'] = "Echec de l'annulation de parcelle !";
                        header('Location: ../../../views/administrateur/Site_web_admin/Reservation.php');
                        exit;

                    }
                }
            }

            if (!empty($EmailWarn)) {

                $_SESSION['EmailWarn'] = $EmailWarn;
                header('Location: MailWarnAutomatise.php');
                exit;

            } else if(!empty($Email)){

                $_SESSION['Email'] = $Email;
                header('Location: MailAnnulationAutomatise.php');
                exit;
                
            }

        } catch (Exception $exception) {
            die('Erreur : ' . $exception->getMessage());
        }