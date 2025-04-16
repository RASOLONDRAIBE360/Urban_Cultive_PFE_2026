<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'Inscription</title>
    <link rel="stylesheet" href="../Client/css/FormulaireInscription.css">
</head>
<body>
    <?php session_start();?>

    <div class="signup-container">
        <form class="signup-form" action="../CRUD/Inscription/CreateInscription.php" method="post">
            <h2>Créer un compte</h2>

            <?php if(isset($_SESSION["success"])) :?>
                <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">

                          <?php echo $_SESSION["success"];?>

                </p>
                
                <?php unset($_SESSION["success"]);?>
            <?php endif; ?>

            <!-- Champ pour le nom -->
            <div class="input-container">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" placeholder="Entrez votre Nom" required>
            </div>

            <!-- Champ pour le prénom -->
            <div class="input-container">
                <label for="prenom">Prenom</label>
                <input type="text" id="prenom" name="prenom" placeholder="Entrez votre Prenom" required>
            </div>

            <!-- Champ pour la date de naissance -->
            <div class="input-container">
                <label for="date">Date de naissance</label>
                <input type="date" id="date" name="date" required>
            </div>

            <!-- Champ pour l'email -->
            <div class="input-container">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Entrez votre email" required>

                <?php if(isset($_SESSION["erreurEmail"])) :?>
                    <p style="color: red; font-weight: bold;"><?php echo $_SESSION["erreurEmail"];?></p>
                    <?php unset($_SESSION["erreurEmail"]);?>
                <?php endif; ?>

            </div>

            <!-- Champ pour le mot de passe -->
            <div class="input-container">
                <label for="motDePasse">Mot de passe</label>
                <input type="password" id="motDePasse" name="motDePasse" placeholder="Créez un mot de passe" required>
                
                <?php if(isset($_SESSION["erreurPwd1"])) :?>
                    <p style="color: red; font-weight: bold;"><?php echo $_SESSION["erreurPwd1"];?></p>
                    <?php unset($_SESSION["erreurPwd1"]);?>
                <?php endif; ?>
                
            </div>

            <!-- Champ pour la confirmation du mot de passe -->
            <div class="input-container">
                <label for="conf_password">Confirmer le mot de passe</label>
                <input type="password" id="conf_password" name="conf_password" placeholder="Confirmez votre mot de passe" required>

                <?php if(isset($_SESSION["erreurPwd2"])) :?>
                    <p style="color: red; font-weight: bold;"><?php echo $_SESSION["erreurPwd2"];?></p>
                    <?php unset($_SESSION["erreurPwd2"]);?>
                <?php endif; ?>

            </div>

            <!-- Bouton pour soumettre le formulaire -->
            <div class="form-actions">
                <button type="submit">S'inscrire</button>
            </div>

            <!-- Lien pour la page de connexion -->
            <div class="login-link">
                <p>Vous avez déjà un compte ? <a href="FormulaireConnexion.php">Se connecter</a></p>
            </div>
        </form>
    </div>
</body>
</html>
