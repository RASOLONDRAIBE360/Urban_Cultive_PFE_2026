<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partage de Conseils de Jardinage</title>
    <link rel="stylesheet" href="../css/Commentaire.css">
    <link rel="stylesheet" href="../css/Footer.css">
</head>
<body>

    <?php session_start();?>

    <?php require_once(__DIR__.'/../CRUD/Communaute/SelectPartielleAvis.php');?>
    <?php require_once(__DIR__.'/../CRUD/Communaute/SelectAvis.php');?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="container">
        <h1>Avis et expérience sur les parcelles louées.</h1>
        <p>Partagez vos opinions avec la communauté !</p>
    </div>

        <main>
            
            <?php if(isset($_SESSION['successPublication'])) :?>
                <p style="color: green; 
                            font-weight: bold; 
                            text-align: center;
                            position: relative;
                            bottom: 10px;">
                            
                            <?php echo $_SESSION['successPublication'];?>

                </p>
                <?php unset($_SESSION['successPublication']);?>
            <?php endif;?>
            
            <?php if(isset($_SESSION['erreurPublication'])) :?>
                <p style="color: red; 
                            font-weight: bold; 
                            text-align: center;
                            position: relative;
                            bottom: 10px;">
                            
                            <?php echo $_SESSION['erreurPublication'];?>

                </p>
                <?php unset($_SESSION['erreurPublication']);?>
            <?php endif;?>

            <div class="container">
                <!-- Formulaire pour partager un conseil -->
                <section class="partage-conseil">
                    
                    <form action="../CRUD/Communaute/CreateAvis.php" method="post">
                        
                        <input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION['user_id_user'];?>">

                        <label for="id_parc">Id parcelle :</label>
                        <select id="id_parc" name="id_parc" class="parcelle-select">
                            
                            <?php foreach($listeUtilisateurs as $utilisateur) : ?>
                                <option value="<?php echo $utilisateur['Id_parc']?>"><?php echo $utilisateur['Id_parc']?></option>
                            <?php endforeach;?>

                        </select>

                        <label for="avis">Votre Avis :</label>
                        <textarea id="avis" name="avis" placeholder="Laisser votre avis..." rows="4" required></textarea>

                        <button type="submit">Partager</button>
                    </form>
                </section>
            </div>
        </main>
   
                <!-- Section des conseils partagés -->
                <section class="conseils">
                    <h2>Vos avis :</h2>

                    <!-- Exemple de conseil -->

                    <?php if(isset($_SESSION['successSuppression'])) :?>
                        <p style="color: green; 
                                    font-weight: bold; 
                                    text-align: center;
                                    position: relative;
                                    bottom: 10px;">
                                    
                                    <?php echo $_SESSION['successSuppression'];?>

                        </p>
                        <?php unset($_SESSION['successSuppression']);?>
                    <?php endif;?>
                    
                    <?php if(isset($_SESSION['erreurSuppression'])) :?>
                        <p style="color: red; 
                                    font-weight: bold; 
                                    text-align: center;
                                    position: relative;
                                    bottom: 10px;">
                                    
                                    <?php echo $_SESSION['erreurSuppression'];?>

                        </p>
                        <?php unset($_SESSION['erreurSuppression']);?>
                    <?php endif;?>
                    
                    <?php foreach ($utilisateurs as $utilisateur) :?>

                    <div class="conseil">
                        <div class="conseil-header">

                            <span class="auteur"><?php echo $_SESSION['email_user'];?></span>
                            <span class="parcelle"><?php echo $utilisateur['Id_parc'];?></span>
                            <span class="date"><?php echo $utilisateur['Date'];?></span>

                        </div>
                        <p class="texte-conseil"><?php echo $utilisateur['Avis'];?></p>

                        <form action="../CRUD/Communaute/DeleteAvis.php" method="post">

                            <input type="hidden" name="date" value="<?php echo $utilisateur['Date'];?>">
                            <button type="submit">Supprimer</button>

                        </form>

                    </div>
                    
                    <?php endforeach;?>
                </section>

        <?php require_once(__DIR__.'/../Footer/Footer.php')?>
    
    <script src="../Javascript/Commentaire.js"></script>
</body>
</html>
