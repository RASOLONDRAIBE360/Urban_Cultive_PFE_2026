<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../../public/assets/css/administrateur/Accueil.css">
    <link rel="stylesheet" href="../../../public/assets/css/administrateur/Modal_update.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../../public/assets/css/general/output.css">
</head>
<body>
    <?php session_start(); ?>
    <?php require_once(__DIR__.'/../../../models/CRUD/administrateur/Reservations/SelectReservation.php')?>
    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>
    <?php require_once(__DIR__.'/../../../models/CRUD/administrateur/Reservations/CountReservation.php')?>
    <?php require_once(__DIR__.'/../../../models/CRUD/administrateur/Locataires/CountLocataires.php')?>
    <?php require_once(__DIR__.'/../../../models/CRUD/administrateur/Parcelles/CountParcelles.php')?>

    <div class="main-content">
        <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Reservation(s)</h2>

        <?php if(isset($_SESSION['successUpdateValidation'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successUpdateValidation'];?>

            </p>
            <?php unset($_SESSION['successUpdateValidation']);?>
        <?php endif;?>
        <?php if(isset($_SESSION['erreurUpdateValidation'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurUpdateValidation'];?>

            </p>
            <?php unset($_SESSION['erreurUpdateValidation']);?>
        <?php endif;?>
        <?php if(isset($_SESSION['successEmail'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successEmail'];?>

            </p>
            <?php unset($_SESSION['successEmail']);?>
        <?php endif;?>
        <?php if(isset($_SESSION['erreurEmail'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurEmail'];?>

            </p>
            <?php unset($_SESSION['erreurEmail']);?>
        <?php endif;?>
        <?php if(isset($_SESSION['erreurAnnulationReservation'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurAnnulationReservation'];?>

            </p>
            <?php unset($_SESSION['erreurAnnulationReservation']);?>
        <?php endif;?>

        <table>
            <thead>
                <tr>
                    <th>Id r.</th>
                    <th>Email</th>
                    <th>Nom p.</th>
                    <th>Id p.</th>
                    <th>Prix p.</th>
                    <th>Date r.</th>
                    <th>Duree r.</th>
                    <th>Date Fin</th>
                    <th>Date Limite</th>
                    <th>Status r.</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $reservations = $_SESSION['reservations'];?>

                <?php foreach($reservations as $reservation) : ?>
                    <?php 
                        $today = new DateTime();
                        $todayFormatted = $today->format('Y-m-d');

                        if ($reservation['Date_limite'] <= $todayFormatted AND $reservation['Status_envoie'] == 0){
                            echo "<script> window.location.href = '../../../models/CRUD/mail/MailAutomatise.php'</script>;";
                        } else if ($reservation['Date_fin'] == $todayFormatted AND $reservation['Status_envoie'] == 1){
                            echo "<script> window.location.href = '../../../models/CRUD/mail/MailAutomatise.php'</script>;";
                            //Pour vérifier que le fichier dont j'essaie d'accéder se trouve réellement au bon endroit j'utilise
                            /*var_dump(file_exists(__DIR__.'/../CRUD/MailAutomatise.php'));*/
                        }
                    ?>
                    <?php
                        $two_weeks_later = date("Y-m-d", strtotime("+14 days"));  // Seuil pour l'alerte jaune
                        $one_week_later = date("Y-m-d", strtotime("+7 days"));    // Seuil pour l'alerte rouge

                        $warning_icon = ""; // Par défaut, pas d'icône

                        if ((strtotime($reservation['Date_res']) <= strtotime($one_week_later) && $reservation['Status_res'] == 'attente') || (strtotime($reservation['Date_res']) == strtotime($todayFormatted) && $reservation['Status_res'] == 'attente')) {
                            $warning_icon = "<i class='fas fa-exclamation-circle' style='color:red;' title='Urgent : Prise à effet de la date dans moins de 7 jours'></i>";
                        } elseif (strtotime($reservation['Date_res']) <= strtotime($two_weeks_later) && $reservation['Status_res'] == 'attente') {
                            $warning_icon = "<i class='fas fa-exclamation-triangle' style='color:orange;' title='Attention : Prise à effet de la date dans moins de 14 jours'></i>";
                        }
                    ?>

                    <tr>
                        <td><?php echo $reservation['Id_res']; ?></td>
                        <td><?php echo $reservation['Email']; ?></td>
                        <td><?php echo $reservation['Nom_parc']; ?></td>
                        <td><?php echo $reservation['Id_parc']; ?></td>
                        <td><?php echo $reservation['Prix_parc']; ?></td>
                        <td><?php echo $reservation['Date_res']. " " . $warning_icon;?></td>
                        <td><?php echo $reservation['Duree_res']; ?></td>
                        <td><?php echo $reservation['Date_fin']; ?></td>
                        <td><?php echo $reservation['Date_limite']; ?></td>
                        <td><?php echo $reservation['Status_res']; ?></td>
                        <td>

                        <form action="../../../models/CRUD/administrateur/Reservations/ValidationReservation.php" method="post">

                            <input type="hidden" name="id_res" value="<?php echo $reservation['Id_res']; ?>">
                            <button class="btn valid-btn" type="submit">VALIDER</button>

                        </form>

                        <form action="../../../models/CRUD/administrateur/Reservations/RefusReservation.php" method="post">

                            <input type="hidden" name="id_res" value="<?php echo $reservation['Id_res']; ?>">
                            <button class="btn delete-btn" type="submit">REFUSER</button>

                        </form>

                        <form action="../../../models/CRUD/administrateur/Reservations/AttenteReservation.php" method="post">

                            <input type="hidden" name="id_res" value="<?php echo $reservation['Id_res']; ?>">
                            <button class="btn edit-btn" type="submit">MISE EN ATTENTE</button>

                        </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <!-- MODAL MIS EN COMMENTAIRE POUR AUTOMATISATION DES MAILS -->
    <!--
    <div class="form-container" id="modal1">
        <div class="modal-content">
        <h1> VALIDATION RESERVATION </h1>
        <span class="fermer" onclick="fermerModal('modal1')">&times;</span>
        <form action="../../../models/CRUD/mail/Mail.php" method="post">
            <label for="objet">Objet</label>
            <input type="text" id="objet" name="objet">
            <label for="message">Message</label>
            <textarea id="message" name="message" cols="65" rows="5" required></textarea>
            <button type="submit">Envoyer</button>
        </form>
        </div>
    </div>
    -->

    <?php if(isset($_SESSION['successValidation'])) :?>
        <p style="color: green; font-weight: bold; text-align: center; position: relative; bottom: 10px;">
            <?php echo $_SESSION['successValidation'];?>
        </p>
        <?php unset($_SESSION['successValidation']);?>
    <?php endif;?>

    <?php if(isset($_SESSION['erreurUpdateValidation'])) :?>
        <p style="color: red; font-weight: bold; text-align: center; position: relative; bottom: 10px;">
            <?php echo $_SESSION['erreurUpdateValidation'];?>
        </p>
        <?php unset($_SESSION['erreurUpdateValidation']);?>
    <?php endif;?>

    <?php if(isset($_SESSION['successEmail'])) :?>
        <p style="color: green; font-weight: bold; text-align: center; position: relative; bottom: 10px;">
            <?php echo $_SESSION['successEmail'];?>
        </p>
        <?php unset($_SESSION['successEmail']);?>
    <?php endif;?>

    <?php if(isset($_SESSION['erreurEmail'])) :?>
        <p style="color: red; font-weight: bold; text-align: center; position: relative; bottom: 10px;">
            <?php echo $_SESSION['erreurEmail'];?>
        </p>
        <?php unset($_SESSION['erreurEmail']);?>
    <?php endif;?>

    <script src="../../../public/assets/javascript/administrateur/Accueil.js"></script>
</body>
</html>