<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jardins Urbains</title>
    <link rel="stylesheet" href="../css/Accueil.css">
    <link rel="stylesheet" href="../css/AccueilModal.css">
    <link rel="stylesheet" href="../css/Footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php session_start();?>
    
    <?php require_once(__DIR__.'/../CRUD/Parcelles/SelectSpecifiqueParcelle.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <?php if(isset($_SESSION['successAnnulationRenouv'])) :?>
        <p style="color: green; 
                    font-weight: bold; 
                    text-align: center;
                    position: relative;
                    bottom: 10px;">
                    
                    <?php echo $_SESSION['successAnnulationRenouv'];?>

        </p>
        <?php unset($_SESSION['successAnnulationRenouv']);?>
    <?php endif;?>
    <?php if(isset($_SESSION['erreurAnnulationRenouv'])) :?>
        <p style="color: red; 
                    font-weight: bold; 
                    text-align: center;
                    position: relative;
                    bottom: 10px;">
                    
                    <?php echo $_SESSION['erreurAnnulationRenouv'];?>

        </p>
        <?php unset($_SESSION['erreurAnnulationRenouv']);?>
    <?php endif;?>
    
    <?php foreach($MyParcelles as $MyParcelle) :?>
        
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $MyParcelle['Id_parc'];?>')">
            <img src="<?php echo $MyParcelle['Chemin_image'];?>" alt="Image de la parcelle">
            <div class="card-content">

                <h3><?php echo $MyParcelle['Id_parc'];?></h3>

                <p><?php echo $MyParcelle['Taille_parc'];?>m²</p>
                <p><?php echo $MyParcelle['Prix_parc'];?>€/mois</p>

                <?php 
                    $dateLimite = $MyParcelle['Date_limite'];
                    $today = new DateTime();
                    $today = $today->format('Y-m-d');

                    $warning_icon = ""; // Par défaut, pas d'icône

                    // Calcul de la date limite moins 3 jours
                    $date_warning = date('Y-m-d', strtotime($dateLimite));

                    if ($today >= $date_warning && $MyParcelle['Status_res'] == 'valide') {
                        $warning_icon = "<i class='fas fa-exclamation-triangle' style='color:orange;' title='Attention : Expiration de la réservation dans 3 jours. Veuillez la renouveler !!!'></i>";
                    }
                ?>

                <p class="date-fin" data-date="<?php echo htmlspecialchars($MyParcelle['Date_limite']);?>">
                    <?php echo htmlspecialchars($MyParcelle['Date_fin']) . " " . $warning_icon;?>
                </p>

            </div>
        </div>
    </section>
    
    <?php endforeach;?>
    
    <!-- Modals --> 

    <?php foreach($MyParcelles as $MyParcelle):?>
        <div id="<?php echo $MyParcelle['Id_parc'];?>" class="modal">
            <div class="modal-content">

                <span class="fermer" onclick="fermerModal('<?php echo $MyParcelle['Id_parc'];?>')">&times;</span>
                <h2><?php echo $MyParcelle['Id_parc']?></h2>

                <?php if(isset($_SESSION['successRenewal'])) :?>
                    <p style="color: green; 
                                font-weight: bold; 
                                text-align: center;
                                position: relative;
                                bottom: 10px;">
                                
                                <?php echo $_SESSION['successRenewal'];?>

                    </p>
                    <?php unset($_SESSION['successRenewal']);?>
                <?php endif;?>
                
                <?php if(isset($_SESSION['erreurRenewal'])):?>
                    <p style="color: red; 
                                font-weight: bold; 
                                text-align: center;
                                position: relative;
                                bottom: 10px;">
                                
                                <?php echo $_SESSION['erreurRenewal'];?>

                    </p>
                    <?php unset($_SESSION['erreurRenewal']);?>
                <?php endif;?>

                <img src="<?php echo $MyParcelle['Chemin_image'];?>" alt="Imaged de la parcelle" style="border-radius: 8px; width: 300px; height: auto; ">
                <p><strong>Surface : </strong><?php echo $MyParcelle['Taille_parc'];?>m²</p>
                <p><strong>Exposition : </strong><?php echo $MyParcelle['Exposition'];?></p>
                <p><strong>Équipements : </strong><?php echo $MyParcelle['Equipements'];?></p>
                <p><strong>Idéal pour : </strong><?php echo $MyParcelle['Preferences'];?></p>
                <p><strong>Prix : </strong><?php echo $MyParcelle['Prix_parc'];?>€/mois</p>
                <p><?php echo $MyParcelle['Description'];?></p>
                
                <?php $today = new DateTime();
                      $today = $today->format('Y-m-d'); // Convertit today en une chaîne de caractères avec le format voulu;?>
                    

                <?php if($MyParcelle['Date_limite'] <= $today):?>

                    <form action="../CRUD/Reservations/DeleteReservation.php" method="post">

                        <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $MyParcelle['Id_parc']; ?>">
                        <button class="avis-btn" type="submit">Annuler</button>

                    </form>
                    <form action="../CRUD/Reservations/SelectReservationToRenewal.php" method="post">

                        <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $MyParcelle['Id_parc']; ?>">
                        <button type="submit" class="reservation-btn" style="padding: 15px 58px;">Renouveller</button>

                    </form>
                
                <?php endif;?>

            </div>
        </div>
    <?php endforeach;?>

        <div class="footer">
            <?php require_once(__DIR__.'/../Footer/Footer.php');?>
        </div>
    <script src="../Javascript/Parcelle.js"></script>
</body>
</html>
