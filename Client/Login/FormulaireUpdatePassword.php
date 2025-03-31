<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de modification mot de passe</title>
    <link rel="stylesheet" href="../css/FormulaireUpdatePassword.css">
</head>
<body>
    <div class="login-container">
        <form class="login-form" action="../GestionPassword/UpdatePassword.php" method="post">
            
                <a id="back" href="Formulaire_connexion.php">Retour</a>
            
            <h2>Modification Mot de Passe</h2>

            <div class="input-container">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Entrez votre Email Adresse">
            </div>

            <div class="input-container">
                <label for="new_pwd">Nouveau Mot de passe</label>
                <input type="password" id="new_pwd" name="new_pwd" required placeholder="Entrez le nouveau mot de passe">
            </div>

            <div class="input-container">
                <label for="conf_pwd">Retaper Mot de passe</label>
                <input type="password" id="conf_pwd" name="conf_pwd" required placeholder="Confirmer le mot de passe">
            </div>


            <div class="form-actions">
                <button type="submit">Valider</button>
            </div>

            <!-- Option "S'inscrire" ajoutée ici -->
            <div class="signup-link">
                <p>Pas encore inscrit ? <a href="Formulaire_inscription.php">S'inscrire</a></p>
            </div>
        </form>
    </div>
</body>
</html>
