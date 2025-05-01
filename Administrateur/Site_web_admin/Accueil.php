<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
    <link rel="stylesheet" href="../css/Accueil.css">
    <link rel="stylesheet" href="../css/Modal_update.css">
</head>
<body>
    <?php session_start(); ?>
    <?php require_once(__DIR__.'/../CRUD/Locataires/SelectLocataires.php')?>
    <?php require_once(__DIR__.'/../CRUD/Locataires/CountLocataires.php')?>
    <?php require_once(__DIR__.'/../CRUD/Reservations/CountReservation.php')?>
    <?php require_once(__DIR__.'/../CRUD/Parcelles/CountParcelles.php')?>

    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>

    <div class="main-content">
    
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Utilisateur(s)</h2>
        
        <?php if(isset($_SESSION['successSuppression'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successSuppression'];?>

            </p>
            <?php unset($_SESSION['successSuppression']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurSuppression'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurSuppression'];?>

            </p>
            <?php unset($_SESSION['erreurSuppression']);?>
        <?php endif;?>

        <table>
            <thead>
                <tr>
                    <th>User_ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>N° tel</th>
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
                        <td><?php echo $utilisateur['Num_tel']; ?></td>
                        <td><?php echo $utilisateur['Date_Naissance']; ?></td>
                        <td><?php echo $utilisateur['Email']; ?></td>
                        <td><?php echo $utilisateur['Role']; ?></td>
                        <td>
                            <form action="../CRUD/Locataires/SelectPartielleLocataires.php" method="post">

                                <input type="hidden" name="user_id" value="<?php echo $utilisateur['User_id']; ?>">
                                <button class="btn edit-btn Sbtn" type="submit">Modifier</button>

                            </form>

                            <form action="../CRUD/Locataires/SuppressionLocataires.php" method="post">
                                
                                <input type="hidden" name="email" value="<?php echo $utilisateur['Email']; ?>">

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

    <?php if(isset($_SESSION['successValidation'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successValidation'];?>

            </p>
            <?php unset($_SESSION['successValidation']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurValidation'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurValidation'];?>

            </p>
            <?php unset($_SESSION['erreurValidation']);?>
        <?php endif;?>
    
    <?php if(isset($_SESSION['successUpdate'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successUpdate'];?>

            </p>
            <?php unset($_SESSION['successUpdate']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurUpdate'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurUpdate'];?>

            </p>
            <?php unset($_SESSION['erreurUpdate']);?>
        <?php endif;?>

    <?php if(isset($_SESSION['successValidation'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successValidation'];?>

            </p>
            <?php unset($_SESSION['successValidation']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurValidation'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurValidation'];?>

            </p>
            <?php unset($_SESSION['erreurValidation']);?>
        <?php endif;?>
    
    <?php if(isset($_SESSION['successUpdate'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successUpdate'];?>

            </p>
            <?php unset($_SESSION['successUpdate']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurUpdate'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurUpdate'];?>

            </p>
            <?php unset($_SESSION['erreurUpdate']);?>
        <?php endif;?>

    <?php if(isset($_SESSION['successValidation'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successValidation'];?>

            </p>
            <?php unset($_SESSION['successValidation']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurValidation'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurValidation'];?>

            </p>
            <?php unset($_SESSION['erreurValidation']);?>
        <?php endif;?>
    
    <form action="../CRUD/Locataires/ModificationLocataires.php" method="post">
        
        <label for="id">ID Utilisateur</label>
        <select id="id" name="id">
            <option><?php echo $_SESSION['my_user_id'];?></option>
        </select>
        
        <?php $UDatas=$_SESSION['UDatas'];?>
        
        <?php foreach($UDatas as $UData):?>

            <label for="nom">Nom</label>
            <input type="text" id="nom" name="nom" value="<?php echo $UData['Nom'];?>" placeholder="....." required>

            <label for="prenom">Prénom</label>
            <input type="text" id="prenom" name="prenom" value="<?php echo $UData['Prenom'];?>" placeholder="....." required>

            <label for="num_tel">N°tel</label>
            <input type="tel" id="num_tel" name="num_tel" placeholder="+XXX XX XX XX XX" value="<?php echo $UData['Num_tel'];?>" required
            pattern="^\+?[0-9\s\-]{10,15}$">

            <label for="date">Date de Naissance</label>
            <input type="date" id="date" name="date" value="<?php echo $UData['Date_Naissance'];?>" placeholder="....." required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo $UData['Email'];?>" placeholder="....." required>
        
        <?php endforeach;?>

        <button type="submit">Enregistrer</button>
    </form>
    </div>
</div>
    
<script src="../Javascript/Accueil.js"></script>
</body>
</html>
