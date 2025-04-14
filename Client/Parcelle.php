<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jardins Urbains</title>
    <link rel="stylesheet" href="css/Parcelle.css">
    <link rel="stylesheet" href="css/ParcelleModal.css">
    <link rel="stylesheet" href="css/Footer.css">
</head>
<body>

    <?php session_start();?>
    
    <?php require_once(__DIR__.'/CRUD/Parcelles/SelectPartielleParcelle.php')?>
    <?php require_once(__DIR__.'/Header/Header.php')?>
    
    <?php $Parcelles1 = $_SESSION['Parcelles1'];?>
    <?php foreach($Parcelles1 as $Parcelle1) :?>
        
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $Parcelle1['Id_parc'];?>')">
            <img src="Projet_HTML_CSS/Photo1.jpg" alt="Parcelle Zen">
            <div class="card-content">

                <h3><?php echo $Parcelle1['Id_parc'];?></h3>

                <p><?php echo $Parcelle1['Taille_parc'];?>m²</p>
                <p><?php echo $Parcelle1['Exposition'];?></p>
                <p><?php echo $Parcelle1['Prix_parc'];?>€/mois</p>

            </div>
        </div>
    </section>
    
    <?php endforeach;?>
    
    <!-- Modals --> 

    <?php foreach($Parcelles1 as $Parcelle1):?>
        <div id="<?php echo $Parcelle1['Id_parc'];?>" class="modal">
            <div class="modal-content">

                <span class="fermer" onclick="fermerModal('<?php echo $Parcelle1['Id_parc'];?>')">&times;</span>
                <h2><?php echo $Parcelle1['Id_parc']?></h2>
                <img src="Projet_HTML_CSS/Photo1.jpg" alt="Parcelle Zen" style="width: 100%; border-radius: 8px;">
                <p><strong>Surface : </strong><?php echo $Parcelle1['Taille_parc'];?>m²</p>
                <p><strong>Exposition : </strong><?php echo $Parcelle1['Exposition'];?></p>
                <p><strong>Équipements : </strong><?php echo $Parcelle1['Equipements'];?></p>
                <p><strong>Idéal pour : </strong><?php echo $Parcelle1['Preferences'];?></p>
                <p><strong>Prix : </strong><?php echo $Parcelle1['Prix_parc'];?>€/mois</p>
                <p><?php echo $Parcelle1['Description'];?></p>

                <form action="" method="post">

                    <button class="avis-btn" onclick='window.location.href="Avis.php"'>Annuler</button>

                </form>
                <form action="CRUD/Reservations/SelectPartielleReservation.php" method="post">

                    <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $parcelle['Id_parc']; ?>">
                    <button type="submit" class="reservation-btn">Renouveller</button>

                </form>

            </div>
        </div>
    <?php endforeach;?>

        <div class="footer">
            <?php require_once(__DIR__.'/Footer.php');?>
        </div>
    <script src="Javascript/Parcelle.js"></script>
</body>
</html>
