<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jardins Urbains</title>
    <link rel="stylesheet" href="../css/Accueil.css">
    <link rel="stylesheet" href="../css/AccueilModal.css">
    <link rel="stylesheet" href="../css/Footer.css">
</head>
<body>

    <?php session_start();?>
    
    <?php require_once(__DIR__.'/../CRUD/Parcelles/SelectSpecifiqueParcelle.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>
    
    <?php foreach($MyParcelles as $MyParcelle) :?>
        
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $MyParcelle['Id_parc'];?>')">
            <img src="<?php echo $MyParcelle['Chemin_image'];?>" alt="Image de la parcelle">
            <div class="card-content">

                <h3><?php echo $MyParcelle['Id_parc'];?></h3>

                <p><?php echo $MyParcelle['Taille_parc'];?>m²</p>
                <p><?php echo $MyParcelle['Exposition'];?></p>
                <p><?php echo $MyParcelle['Prix_parc'];?>€/mois</p>

                <p class="date-fin" data-date="<?php echo date('Y-m-d', strtotime($MyParcelle['Date_fin'])); ?>">
                    <?php echo $MyParcelle['Date_fin']; ?>
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

                <img src="<?php echo $MyParcelle['Chemin_image'];?>" alt="Imaged de la parcelle" style="border-radius: 8px; width: 300px; height: auto; ">
                <p><strong>Surface : </strong><?php echo $MyParcelle['Taille_parc'];?>m²</p>
                <p><strong>Exposition : </strong><?php echo $MyParcelle['Exposition'];?></p>
                <p><strong>Équipements : </strong><?php echo $MyParcelle['Equipements'];?></p>
                <p><strong>Idéal pour : </strong><?php echo $MyParcelle['Preferences'];?></p>
                <p><strong>Prix : </strong><?php echo $MyParcelle['Prix_parc'];?>€/mois</p>
                <p><?php echo $MyParcelle['Description'];?></p>

                <?php if(strtotime($MyParcelle['Date_fin']) == strtotime(date("Y-m-d"))) :?>

                    <form action="../CRUD/Reservations/DeleteReservation.php" method="post">

                        <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $MyParcelle['Id_parc']; ?>">
                        <button class="avis-btn" type="submit">Annuler</button>

                    </form>
                    <form action="../CRUD/Reservations/SelectPartielleReservation.php" method="post">

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
