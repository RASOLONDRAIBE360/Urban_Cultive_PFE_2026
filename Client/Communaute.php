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
    
    <?php require_once(__DIR__.'/Header/Header.php')?>

    <div class="container">
        <h1>Partagez vos Conseils de Jardinage</h1>
        <p>Échangez vos astuces et conseils pour un jardinage réussi !</p>
    </div>

        <?php require_once(__DIR__.'/html/Formulaire_commentaire.html');?>
        <?php require_once(__DIR__.'/CRUD/Select_commentaire.php');?>

            <!-- Section des conseils partagés -->
            <section class="conseils">
                <h2>Conseils récents</h2>

                <!-- Exemple de conseil -->

                <?php foreach ($listeUtilisateurs as $utilisateur) :?>

                <div class="conseil">
                    <div class="conseil-header">

                        <span class="auteur"><?php echo $utilisateur['Email'];?></span>
                        <span class="date"><?php echo $utilisateur['Date'];?></span>
                    </div>
                    <h3 class="titre-conseil"><?php echo $utilisateur['Titre'];?></h3>
                    <p class="texte-conseil"><?php echo $utilisateur['Conseil'];?></p>

                    <form action="CRUD/DeleteCommentaire.php" method="post">

                        <input type="hidden" name="email" value="<?php echo $utilisateur['Email'];?>">
                        <input type="hidden" name="date" value="<?php echo $utilisateur['Date'];?>">
                        <button type="submit">Supprimer</button>

                    </form>

                </div>
                
                <?php endforeach;?>
            </section>
        </div>
    </main>

    <?php require_once(__DIR__.'/Footer.php')?>
    
    <script src="Javascript/Commentaire.js"></script>
</body>
</html>
