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
    
    <?php require_once(__DIR__.'/../../../models/CRUD/client/parcelles/SelectParcelle.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <section class="hero">
    
    <?php if(isset($_SESSION['nom_user']) && isset($_SESSION['prenom_user'])): ?>
        <h1> Bonjour, <span style="font-weight: bold; font-size: 24px; color:#2d6a4f; font-style: italic; text-transform: capitalize; padding-left: 10px;"><?php echo "{$_SESSION['nom_user']} {$_SESSION['prenom_user']}";?></span></h1>
    <?php else : ?>
        <h1><?php echo "Bienvenue sur mon site";?></h1>
    <?php endif;?>

        <h1>Trouvez votre <span style="color:#2d6a4f">parcelle idéale</span></h1>
        <p>Explorez notre sélection de parcelles urbaines et donnez vie à votre projet de jardinage</p>
    </section>
    
    <?php foreach($parcelles as $parcelle) :?>
        
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('<?php echo $parcelle['Id_parc'];?>')">
            <?php $imagePath = str_replace(["../../Upload/", "../../../public/uploads/"], "../../../public/uploads/", $parcelle['Chemin_image']); ?>
            <img src="<?php echo $imagePath;?>" alt="Image de la parcelle">
            <div class="card-content relative p-4 top-[10px] tracking-wide">
                <h3 class="font-bold text-[25px] relative bottom-[15px]"><?php echo $parcelle['Id_parc'];?></h3>

                <p class="<?php echo ($parcelle['Status_parc'] == 'occupe') ? 'occupe' : 'disponible';?> mb-[20px]"><?php echo $parcelle['Status_parc'];?></p>
                <p><?php echo $parcelle['Taille_parc'];?>m²</p>
                <p><?php echo $parcelle['Prix_parc'];?> Rs/mois</p>

            </div>
        </div>
    </section>
    <?php endforeach;?>
    
    <!-- Modals --> 

<?php foreach($parcelles as $parcelle):?>
    <div id="<?php echo $parcelle['Id_parc'];?>" class="modal">
        <div class="modal-content">
            <span class="fermer" onclick="fermerModal('<?php echo $parcelle['Id_parc'];?>')">&times;</span>
            <h2 class="font-bold text-[25px]"><?php echo $parcelle['Id_parc']?></h2>
            <?php $imagePath = str_replace(["../../Upload/", "../../../public/uploads/"], "../../../public/uploads/", $parcelle['Chemin_image']); ?>
            <img src="<?php echo $imagePath;?>" alt="Image de la parcelle" class="rounded-[8px] w-[300px] h-auto">

            <p><strong>Surface : </strong><?php echo $parcelle['Taille_parc'];?>m²</p>
            <p><strong>Exposition : </strong><?php echo $parcelle['Exposition'];?></p>
            <p><strong>Équipements : </strong><?php echo $parcelle['Equipements'];?></p>
            <p><strong>Idéal pour : </strong><?php echo $parcelle['Preferences'];?></p>
            <p><strong>Prix : </strong><?php echo $parcelle['Prix_parc'];?> Rs/mois</p>
            <p><?php echo $parcelle['Description'];?></p>

            <form action="../../../models/CRUD/client/communautes/SelectSpecificAvis.php" method="post">

                <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $parcelle['Id_parc']; ?>">
                <button type="submit" class="avis-btn">Voir les avis</button>

            </form>

            <form action="../../../models/CRUD/client/reservations/SelectPartielleReservation.php" method="post">

                <input type="hidden" id="id_parc" name="id_parc" value="<?php echo $parcelle['Id_parc']; ?>">
                <?php $isOccupied = ($parcelle['Status_parc'] == 'occupe') ? "disabled" : "";?>

                <button type="submit" class="reservation-btn" <?php echo $isOccupied; ?>>Réserver cette parcelle</button>

            </form>
        </div>
    </div>
<?php endforeach;?>

    <?php require_once(__DIR__.'/../Footer/Footer.php');?>

<script src="../../../public/assets/javascript/client/Accueil.js"></script>
</body>
</html>
