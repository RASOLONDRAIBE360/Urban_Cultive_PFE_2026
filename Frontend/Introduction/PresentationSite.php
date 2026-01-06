<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanCultive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Client/css/AccueilModal.css">
    <link rel="stylesheet" href="../Client/css/PresentationSite.css">
    <link rel="stylesheet" href="../Style/output.css">
</head>
<body>

    <?php session_start();?>

    <?php require_once(__DIR__."/../HeaderIntroduction/Header.php");?>
    <section class="hero" id="Accueil">
        <div class="background-overlay"></div>
        <div class="hero-content">
            <h1> GESTION PARTICIPATIVE DE PARCELLE URBAINE </h1>
            <p>
                Une plateforme de gestion participatif de parcelle 
                urbaine qui offre à l’utilisateur la possibilité de consulter, 
                de réserver ainsi que de partager des avis sur des parcelles spécifiques.
            </p>
            <div class="buttons">
                <button class="animated-btn" id="button1" onclick='window.location.href="../Login/FormulaireInscription.php"'>Créer un compte</button>
            </div>
        </div>
    </section>
    <section class="parcelles" id="Parcelles">

        <?php require(__DIR__."/../CRUD/Parcelle/SelectParcelleIntroduction.php");?>

        <h1 class="position relative text-3xl"> Découvrez nos différentes parcelles </h1>
        <?php 
        // Limiter l'affichage à seulement 3 parcelles
        $parcellesAffichees = array_slice($parcelles, 0, 4); 
        ?>
        <?php foreach($parcellesAffichees as $parcelle) :?>
            <?php
            $imagePath = str_replace("../../", "../", $parcelle['Chemin_image']);
            ?>
            <div class="card" onclick="ouvrirModal('<?php echo $parcelle['Id_parc'];?>')">
                <img src="<?php echo $imagePath;?>" alt="Image de la parcelle">
                <div class="card-content">
                    <h3><?php echo $parcelle['Id_parc'];?></h3>
                    <p><?php echo $parcelle['Taille_parc'];?>m²</p>
                    <p><?php echo $parcelle['Prix_parc'];?> Rs/mois</p>
                </div>
            </div>
        <?php endforeach;?>
    
            <!-- Modals --> 

        <?php foreach($parcellesAffichees as $parcelle):?>
            <?php
            $imagePath = str_replace("../../", "../", $parcelle['Chemin_image']);
            ?>
            <div id="<?php echo $parcelle['Id_parc'];?>" class="modal">
                <div class="modal-content">
                    <span class="fermer" onclick="fermerModal('<?php echo $parcelle['Id_parc'];?>')">&times;</span>
                    <h2><?php echo $parcelle['Id_parc']?></h2>
                    <img src="<?php echo $imagePath;?>" alt="Image de la parcelle" style="border-radius: 8px; width: 300px; height: auto; ">
                    <p><strong>Surface : </strong><?php echo $parcelle['Taille_parc'];?>m²</p>
                    <p><strong>Exposition : </strong><?php echo $parcelle['Exposition'];?></p>
                    <p><strong>Équipements : </strong><?php echo $parcelle['Equipements'];?></p>
                    <p><strong>Idéal pour : </strong><?php echo $parcelle['Preferences'];?></p>
                    <p><strong>Prix : </strong><?php echo $parcelle['Prix_parc'];?> Rs/mois</p>
                    <p><?php echo $parcelle['Description'];?></p>
                </div>
            </div>
        <?php endforeach;?>
    </section>
    <section id="Fonctionnalite">
    <h2>Nos Fonctionnalités</h2>
    <p class="description">Découvrez tous les outils mis à votre disposition pour une gestion optimale et collaborative de vos parcelles.</p>

    <div class="fonctionnalites-grid">
        <div class="bloc">
        <div class="icon">📌</div>
        <div>
            <h3>Réservation en ligne</h3>
            <p>Réservez vos parcelles disponibles en quelques clics.</p>
        </div>
        </div>

        <div class="bloc">
        <div class="icon">🗑️</div>
        <div>
            <h3>Partage d'avis</h3>
            <p>Échangez des conseils sur la culture des parcelles.</p>
        </div>
        </div>

        <div class="bloc">
        <div class="icon">✏️</div>
        <div>
            <h3>Notifications</h3>
            <p>Recevez des alertes importantes par email.</p>
        </div>
        </div>

        <div class="bloc">
        <div class="icon">🏡</div>
        <div>
            <h3>Accès</h3>
            <p>Consultez les informations des parcelles déjà louées.</p>
        </div>
        </div>
    </section>

    <script src="../Client/Javascript/Accueil.js"></script>
    <script src="../Client/Javascript/PresentationSite.js"></script>

</body>
</html>