<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/Avis.css">
    <link rel="stylesheet" href="../css/Footer.css">
</head>
<body>  
    
    <?php session_start();?>
    <button class="avis-btn" onclick="window.location.href='Accueil.php'">Retour</button>

    <?php if(isset($_SESSION['infoAvis'])) :?><!--Si la variable de session est défini alors les instruction 
        qui suivent s'exécuterons comme il se doit-->
        <p style="color: #6f8878;
                font-weight: bold;
                text-align: center;
                position: relative;
                bottom: 10px;
                font-size: 40px">
                    
                    <?php echo $_SESSION['infoAvis'];?>

        </p>
        <?php unset($_SESSION['infoAvis']);?>
    <?php endif;?>
    <?php $Avis = $_SESSION['Avis'];?>

    <?php foreach ($Avis as $avis) :?>

    <div class="conseil">

        <div class="conseil-header">

            <span class="auteur"><?php echo $_SESSION['email_user'];?></span>
            <span class="parcelle"><?php echo $avis['Id_parc'];?></span>
            <span class="date"><?php echo $avis['Date'];?></span>

        </div>
        <p class="texte-conseil"><?php echo $avis['Avis'];?></p>
        <div class="button-container">

            <form action="../CRUD/Communaute/LikeAvis.php" method="post">
                <input type="hidden" name="id_parc" value="<?php echo $avis['Id_parc'];?>">
                <input type="hidden" name="id_avis" value="<?php echo $avis['Id_avis'];?>">
                <input type="hidden" name="type_action" value="like">
                <button type="submit" class="like-btn">👍 <?php echo $avis['NumberLike']; ?></button>
            </form>

            <form action="../CRUD/Communaute/LikeAvis.php" method="post">
                <input type="hidden" name="id_parc" value="<?php echo $avis['Id_parc'];?>">
                <input type="hidden" name="id_avis" value="<?php echo $avis['Id_avis'];?>">
                <input type="hidden" name="type_action" value="dislike">
                <button type="submit" class="dislike-btn">👎 <?php echo $avis['NumberDislike']; ?></button>
            </form>
        
        </div>
        
    </div>

    <?php endforeach;?>

</body>
</html>