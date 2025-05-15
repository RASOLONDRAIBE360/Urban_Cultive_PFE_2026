<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion - Administrateur</title>
    <link rel="stylesheet" href="../Administrateur/css/Connexion.css">
</head>
<body>
    <?php session_start();?>
    <div class="container">
        <div class="left">
            <h2>Se connecter</h2>
            <form action="../CRUD/Connexion/SelectConnexion.php" method="post">
                <div class="input-group">

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>

                    <?php if (isset($_SESSION["erreurEmail"])): ?>
                        <p style="color: red; font-weight: bold;"><?php echo $_SESSION["erreurEmail"]; ?></p>
                        <?php unset($_SESSION["erreurEmail"]); ?>
                    <?php endif; ?>

                </div>
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>

                    <?php if (isset($_SESSION["erreurPwd"])): ?>
                        <p style="color: red; font-weight: bold;"><?php echo $_SESSION["erreurPwd"]; ?></p>
                        <?php unset($_SESSION["erreurPwd"]); ?>
                    <?php endif; ?>

                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>

            <div class="signup-link">
                <p>Pas encore inscrit ? <a href="FormulaireInscription.php">S'inscrire</a></p>
            </div>

        </div>
        <div class="right">
            <h2>Bienvenue sur la plateforme !</h2>
            <p>Connectez-vous pour accéder au site.</p>
        </div>
    </div>
</body>
</html>
