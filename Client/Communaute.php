<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partage de Conseils de Jardinage</title>
    <link rel="stylesheet" href="css/Commentaire.css">
    <link rel="stylesheet" href="css/Footer.css">
</head>
<body>
    <?php session_start();?>

    <?php require_once(__DIR__.'/Header/Header.php')?>

    <div class="container">
        <h1>Avis et expérience sur les parcelles louées.</h1>
        <p>Partagez vos opinions avec la communauté !</p>
    </div>

        <?php require_once(__DIR__.'/CRUD/SelectParcelle.php');?>
        <main>
            <div class="container">
                <!-- Formulaire pour partager un conseil -->
                <section class="partage-conseil">
                    
                    <form action="../CRUD/EnregistrementAvis.php" method="post">
                        
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" required placeholder="Saisir votre mail">
                        
                    
                        <label for="id_parc">Id parcelle :</label>

                        <select id="parcelles" class="parcelle-select">
                            
                            <?php foreach($listeUtilisateurs as $utilisateur) : ?>

                                <option value="<?php echo $utilisateur['Id_parc']?>"><?php echo $utilisateur['Id_parc']?></option>

                            <?php endforeach;?>

                        </select>

                        <label for="avis">Votre Avis :</label>
                        <textarea id="avis" name="avis" required placeholder="Laisser votre avis..." rows="4"></textarea>

                        <button type="submit">Partager</button>
                    </form>
                </section>
            </div>
        </main>
   
                <!-- Section des conseils partagés -->
                <section class="conseils">
                    <h2>Conseils récents</h2>

                    <!-- Exemple de conseil -->

                    <?php foreach ($listeUtilisateurs as $utilisateur) :?>

                    <div class="conseil">
                        <div class="conseil-header">

                            <span class="auteur"><?php echo $_SESSION['email'];?></span>
                            <span class="date"><?php echo $utilisateur['Date'];?></span>

                        </div>
                        <p class="texte-conseil"><?php echo $utilisateur['Avis'];?></p>

                        <form action="CRUD/DeleteAvis.php" method="post">

                            <input type="hidden" name="date" value="<?php echo $utilisateur['Date'];?>">
                            <button type="submit">Supprimer</button>

                        </form>

                    </div>
                    
                    <?php endforeach;?>
                </section>

        <?php require_once(__DIR__.'/Footer.php')?>
    
    <script src="Javascript/Commentaire.js"></script>
</body>
</html>
