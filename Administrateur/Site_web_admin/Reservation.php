<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/Index.css">
</head>
<body>
    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>

    <div class="main-content">
    
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Utilisateur</h2>
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Date de Naissance</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($listeUtilisateurs as $utilisateur) : ?>
                    <tr>
                        <td><?php echo $utilisateur['Nom']; ?></td>
                        <td><?php echo $utilisateur['Prenom']; ?></td>
                        <td><?php echo $utilisateur['Date_Naissance']; ?></td>
                        <td><?php echo $utilisateur['Email']; ?></td>
                        <td><?php echo $utilisateur['Role']; ?></td>
                        <td>
                            <button class="btn edit-btn" onclick="ouvrirModal('modal1')">Modifier</button>

                            <form action="../CRUD/Suppression.php" method="post">

                                <input type="hidden" name="email" value="<?php echo $utilisateur['Email']; ?>">
                                <input type="hidden" name="password" value="<?php echo $utilisateur['Mot_de_Passe']; ?>">

                                <button class="btn delete-btn" type="submit">Supprimer</button>

                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    </div>
    
    <script src="../Javascript/Index.js"></script>
</body>
</html>