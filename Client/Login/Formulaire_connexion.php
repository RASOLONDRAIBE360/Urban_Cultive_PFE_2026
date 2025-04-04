<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion</title>
    <link rel="stylesheet" href="../css/Formulaire_connexion.css">
</head>
<body>

    <div class="login-container">
        <form class="login-form" action="../Submission/Submit_form_connexion.php" method="post">
            <h2>Connexion</h2>

            <div class="input-container">
                <label for="nom">Nom</label>
                <input type="text" id="nom" name="nom" required placeholder="Entrez votre Nom">
            </div>

            <div class="input-container">
                <label for="prenom">Prenom</label>
                <input type="text" id="prenom" name="prenom" required placeholder="Entrez votre Prenom">
            </div>

            <div class="input-container">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Entrez votre Email">
            </div>

            <div class="input-container">
                <label for="motDePasse">Mot de passe</label>
                <input type="password" id="motDePasse" name="motDePasse" required placeholder="Entrez votre mot de passe">
                <a href="FormulaireUpdatePassword.php">Mot de passe oublié ?</a>
            </div>
        
            <div class="form-actions">
                <button type="submit">Se connecter</button>
            </div>

            <!-- Option "S'inscrire" ajoutée ici -->
            <div class="signup-link">
                <p>Pas encore inscrit ? <a href="Formulaire_inscription.php">S'inscrire</a></p>
            </div>
        </form>
    </div>
</body>
</html>
