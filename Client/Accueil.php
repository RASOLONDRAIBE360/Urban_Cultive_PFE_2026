<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jardins Urbains</title>
    <link rel="stylesheet" href="css/Accueil.css">
    <link rel="stylesheet" href="css/AccueilModal.css">
    <link rel="stylesheet" href="css/Footer.css">
</head>
<body>

    <?php session_start();?>
    
    <?php require_once(__DIR__.'/CRUD/Parcelles/SelectParcelle.php')?>
    <?php require_once(__DIR__.'/Header/Header.php')?>

    <section class="hero">
    
    <?php if(isset($_SESSION['nom']) && isset($_SESSION['prenom'])): ?>
        <h1> Bonjour, <span style="font-weight: bold; font-size: 24px; color:#2d6a4f; font-style: italic; text-transform: capitalize; padding-left: 10px;"><?php echo "{$_SESSION['nom']} {$_SESSION['prenom']}";?></span></h1>
    <?php else : ?>
        <h1><?php echo "Bienvenue sur mon site";?></h1>
    <?php endif;?>

        <h1>Trouvez votre <span style="color:#2d6a4f">parcelle idéale</span></h1>
        <p>Explorez notre sélection de parcelles urbaines et donnez vie à votre projet de jardinage</p>
    </section>
    
    <?php $parcelles = $_SESSION['parcelles'];?>

    <?php foreach($parcelles as $parcelle) :?>
        
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $parcelle['Id_parc'];?>')">
            <img src="Projet_HTML_CSS/Photo1.jpg" alt="Parcelle Zen">
            <div class="card-content">
                <h3><?php echo $parcelle['Id_parc'];?></h3>

                <p class="<?php echo ($parcelle['Status_parc'] == 'occupe') ? 'occupe' : 'disponible';?>"><?php echo $parcelle['Status_parc'];?></p>

                <p><?php echo $parcelle['Taille_parc'];?>m²</p>
                <p><?php echo $parcelle['Exposition'];?></p>
                <p><?php echo $parcelle['Prix_parc'];?>€/mois</p>

            </div>
        </div>
    </section>
    <?php endforeach;?>
    
    <!-- Modals --> 

<?php foreach($parcelles as $parcelle):?>
    <div id="<?php echo $parcelle['Id_parc'];?>" class="modal">
        <div class="modal-content">
            <span class="fermer" onclick="fermerModal('<?php echo $parcelle['Id_parc'];?>')">&times;</span>
            <h2><?php echo $parcelle['Id_parc']?></h2>
            <img src="Projet_HTML_CSS/Photo1.jpg" alt="Parcelle Zen" style="width: 100%; border-radius: 8px;">
            <p><strong>Surface : </strong><?php echo $parcelle['Taille_parc'];?>m²</p>
            <p><strong>Exposition : </strong><?php echo $parcelle['Exposition'];?></p>
            <p><strong>Équipements : </strong><?php echo $parcelle['Equipements'];?></p>
            <p><strong>Idéal pour : </strong><?php echo $parcelle['Preferences'];?></p>
            <p><strong>Prix : </strong><?php echo $parcelle['Prix_parc'];?>€/mois</p>
            <p><?php echo $parcelle['Description'];?></p>

            <button class="avis-btn" onclick='window.location.href="Avis.php"'>Voir les avis</button>

            <form action="CRUD/Reservations/SelectPartielleReservation.php" method="post">

                <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $parcelle['Id_parc']; ?>">
                <?php $isOccupied = ($parcelle['Status_parc'] == 'occupe') ? "disabled" : "";?>

                <button type="submit" class="reservation-btn" <?php echo $isOccupied; ?>>Réserver cette parcelle</button>

            </form>
        </div>
    </div>
<?php endforeach;?>

    <div class="footer">
        <?php require_once(__DIR__.'/Footer.php');?>
    </div>
<script src="Javascript/Accueil.js"></script>
</body>
</html>
