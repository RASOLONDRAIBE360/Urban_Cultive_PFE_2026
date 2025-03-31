<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/Modifier.css">
    <style>
        
    </style>
</head>
<body>

    <?php require_once(__DIR__.'/../CRUD/Select.php')?>
    <?php require_once(__DIR__.'/../CRUD/Locataires.php')?>

    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="main-content">
        <h1>Vue d'ensemble</h1>
        <div class="cards">
            <div class="card">
                <h2>Locataire(s)</h2>
                <?php if ($count == 0) :?>
                    <p>La liste utilisateur est vide.</p>
                
                <?php else :?>  
                    <p><?php echo "$count"?></p>
                <?php endif;?>
            </div>
            <div class="card">
                <h2>Reservation(s)</h2>
                <p>320</p>
            </div>
            <div class="card">
                <h2>Parcelle(s)</h2>
                <p>128</p>
            </div>
        </div>

    <div class="recent-orders">
        <h2>Commandes récentes</h2>
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
    
        <!-- Modals -->

<div class="form-container" id="modal1">
    <div class="modal-content">

    <h1> MISE A JOUR </h1>
    <span class="fermer" onclick="fermerModal('modal1')">&times;</span>

    
    <form action="../CRUD/Modification.php" method="post">

        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom">

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom">

        <label for="date">Date de Naissance</label>
        <input type="date" id="date" name="date">

        <label for="email">Email</label>
        <input type="email" id="email" name="email">

        <button type="submit">Enregistrer</button>
    </form>
    </div>
</div>
    
<script src="../Javascript/Index.js"></script>
</body>
</html>
