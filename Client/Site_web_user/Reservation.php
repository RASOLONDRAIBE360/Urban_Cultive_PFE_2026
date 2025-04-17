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
    
    <?php require_once(__DIR__.'/../CRUD/Parcelles/SelectParcelleEnAttente.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>
    
    <?php foreach($Parcelles2 as $Parcelle2) :?>
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $Parcelle2['Id_parc'];?>')">
            <img src="<?php echo $Parcelle2['Chemin_image'];?>" alt="Image de la parcelle">
            <div class="card-content">
                <h3><?php echo $Parcelle2['Id_parc'];?></h3>

                <p class="<?php echo ($Parcelle2['Status_parc'] == 'occupe') ? 'occupe' : 'disponible';?>"><?php echo $Parcelle2['Status_parc'];?></p>

                <p><?php echo $Parcelle2['Taille_parc'];?>m²</p>
                <p><?php echo $Parcelle2['Exposition'];?></p>
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
            <h2><?php echo $Parcelle2['Id_parc']?></h2>
            <img src="<?php echo $Parcelle2['Chemin_image'];?>" alt="Image de la parcelle" style="border-radius: 8px; width: 300px; height: auto; ">
            <p><strong>Surface : </strong><?php echo $Parcelle2['Taille_parc'];?>m²</p>
            <p><strong>Exposition : </strong><?php echo $Parcelle2['Exposition'];?></p>
            <p><strong>Équipements : </strong><?php echo $Parcelle2['Equipements'];?></p>
            <p><strong>Idéal pour : </strong><?php echo $Parcelle2['Preferences'];?></p>
            <p><strong>Prix : </strong><?php echo $Parcelle2['Prix_parc'];?>€/mois</p>
            <p><?php echo $Parcelle2['Description'];?></p>

            <button class="avis-btn" onclick='window.location.href="Avis.php"'>Annuler</button>

            <form action="../CRUD/Reservations/SelectPartielleReservation.php" method="post">

                <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $Parcelle2['Id_parc']; ?>">
                <?php $isOccupied = ($Parcelle2['Status_parc'] == 'occupe') ? "disabled" : "";?>

                <button type="submit" class="reservation-btn" <?php echo $isOccupied; ?>>Modifier</button>

            </form>
        </div>
    </div>
<?php endforeach;?>

    <div class="footer">
        <?php require_once(__DIR__.'/../Footer/Footer.php');?>
    </div>
<script src="../Javascript/Accueil.js"></script>
</body>
</html>
