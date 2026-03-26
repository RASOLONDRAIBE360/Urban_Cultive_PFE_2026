<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jardins Urbains</title>
    <link rel="stylesheet" href="../../../public/assets/css/client/Accueil.css">
    <link rel="stylesheet" href="../../../public/assets/css/client/AccueilModal.css">
    <link rel="stylesheet" href="../../../public/assets/css/client/Footer.css">
    <link rel="stylesheet" href="../../../public/assets/css/general/output.css">
</head>
<body>

    <?php session_start();?>
    
    <?php require_once(__DIR__.'/../../../models/CRUD/client/parcelles/SelectParcelleEnAttente.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>
    
    <?php if(isset($_SESSION['successAnnulation'])) :?>
                    <p style="color: green; 
                                font-weight: bold; 
                                text-align: center;
                                position: relative;
                                bottom: 10px;">
                                
                                <?php echo $_SESSION['successAnnulation'];?>

                    </p>
                    <?php unset($_SESSION['successAnnulation']);?>
    <?php endif;?>
    <?php if(isset($_SESSION['erreurAnnulation'])) :?>
                    <p style="color: red; 
                                font-weight: bold; 
                                text-align: center;
                                position: relative;
                                bottom: 10px;">
                                
                                <?php echo $_SESSION['erreurAnnulation'];?>

                    </p>
                    <?php unset($_SESSION['erreurAnnulation']);?>
    <?php endif;?>
    
    <?php foreach($Parcelles2 as $Parcelle2) :?>
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $Parcelle2['Id_parc'];?>')">
            <?php $imagePath = str_replace(["../../Upload/", "../../../public/uploads/"], "../../../public/uploads/", $Parcelle2['Chemin_image']); ?>
            <img src="<?php echo $imagePath;?>" alt="Image de la parcelle">
            <div class="card-content relative top-[10px] tracking-wide">
                <h3 class="relative font-bold text-[25px]"><?php echo $Parcelle2['Id_parc'];?></h3>

                <p class="<?php echo ($Parcelle2['Status_parc'] == 'occupe') ? 'occupe' : 'disponible';?> py-[15px]"><?php echo $Parcelle2['Status_parc'];?></p>

                <p><?php echo $Parcelle2['Taille_parc'];?>m²</p>
                <p><?php echo $Parcelle2['Prix_parc'];?>€/mois</p>

            </div>
        </div>
    </section>

    <?php endforeach;?>
    
    <!-- Modals --> 

<?php foreach($Parcelles2 as $Parcelle2):?>
    <div id="<?php echo $Parcelle2['Id_parc'];?>" class="modal">
        <div class="modal-content">
            <span class="fermer" onclick="fermerModal('<?php echo $Parcelle2['Id_parc'];?>')">&times;</span>
            <h2 class="font-bold text-[25px]"><?php echo $Parcelle2['Id_parc']?></h2>

            <?php if(isset($_SESSION['successUpdateReservation'])) :?>
                <p style="color: green; 
                            font-weight: bold; 
                            text-align: center;
                            position: relative;
                            bottom: 10px;">
                            
                            <?php echo $_SESSION['successUpdateReservation'];?>

                </p>
                <?php unset($_SESSION['successUpdateReservation']);?>
            <?php endif;?>
            
            <?php if(isset($_SESSION['erreurUpdateReservation'])):?>
                <p style="color: red; 
                            font-weight: bold; 
                            text-align: center;
                            position: relative;
                            bottom: 10px;">
                            
                            <?php echo $_SESSION['erreurUpdateReservation'];?>

                </p>
                <?php unset($_SESSION['erreurUpdateReservation']);?>
            <?php endif;?>

            <?php $imagePath = str_replace(["../../Upload/", "../../../public/uploads/"], "../../../public/uploads/", $Parcelle2['Chemin_image']); ?>
            <img src="<?php echo $imagePath;?>" alt="Image de la parcelle" class="rounded-lg w-[300px] h-auto">
            <p><strong>Surface : </strong><?php echo $Parcelle2['Taille_parc'];?>m²</p>
            <p><strong>Exposition : </strong><?php echo $Parcelle2['Exposition'];?></p>
            <p><strong>Équipements : </strong><?php echo $Parcelle2['Equipements'];?></p>
            <p><strong>Idéal pour : </strong><?php echo $Parcelle2['Preferences'];?></p>
            <p><strong>Prix : </strong><?php echo $Parcelle2['Prix_parc'];?>€/mois</p>
            <p><?php echo $Parcelle2['Description'];?></p>

            <form action="../../../models/CRUD/client/reservations/DeleteReservation.php" method="post">

                <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $Parcelle2['Id_parc']; ?>">
                <button class="avis-btn" type="submit">Annuler</button>

            </form>
            <form action="../../../models/CRUD/client/reservations/SelectReservationToUpdate.php" method="post">

                <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $Parcelle2['Id_parc']; ?>">
                <button type="submit" class="reservation-btn" style="width: 200px;">Modifier</button>

            </form>
        </div>
    </div>
<?php endforeach;?>

    <div class="footer">
        <?php require_once(__DIR__.'/../Footer/Footer.php');?>
    </div>
<script src="../../../public/assets/javascript/client/Accueil.js"></script>
</body>
</html>
