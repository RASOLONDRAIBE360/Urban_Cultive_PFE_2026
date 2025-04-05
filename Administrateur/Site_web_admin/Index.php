<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/Modal_update.css">
    <style>
        
    </style>
</head>
<body>

    <?php require_once(__DIR__.'/../CRUD/Select.php')?>
    <?php require_once(__DIR__.'/../CRUD/Locataires.php')?>
    <?php require_once(__DIR__.'/../CRUD/Reservation.php')?>

    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>

    <div class="main-content">
    
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Utilisateur</h2>
        <table>
            <thead>
                <tr>
                    <th>User_ID</th>
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
                        <td><?php echo $utilisateur['User_id']; ?></td>
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
    
        <!-- Modals -->

<div class="form-container" id="modal1">
    <div class="modal-content">

    <h1> MISE A JOUR </h1>
    <span class="fermer" onclick="fermerModal('modal1')">&times;</span>

    
    <form action="../CRUD/Modification.php" method="post">
    
        <select id="id" name="id">
            <?php foreach($listeUtilisateurs as $utilisateur) : ?>
                <option><?php echo $utilisateur['User_id'];?></option>
            <?php endforeach; ?>
        </select>

        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required>

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" required>

        <label for="date">Date de Naissance</label>
        <input type="date" id="date" name="date" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <button type="submit">Enregistrer</button>
    </form>
    </div>
</div>
    
<script src="../Javascript/Index.js"></script>
</body>
</html>
