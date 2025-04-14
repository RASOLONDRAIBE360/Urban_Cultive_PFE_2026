<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion - Administrateur</title>
    <link rel="stylesheet" href="../css/Connexion.css">
</head>
<body>
    <div class="container">
        <div class="left">
            <h2>Se connecter</h2>
            <form action="../CRUD/Connexion.php" method="post">
                <div class="input-group">

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Se connecter</button>
            </form>
        </div>
        <div class="right">
            <h2>Bienvenue sur la plateforme !</h2>
            <p>Connectez-vous pour accéder à l'administration.</p>
        </div>
    </div>
</body>
</html>
