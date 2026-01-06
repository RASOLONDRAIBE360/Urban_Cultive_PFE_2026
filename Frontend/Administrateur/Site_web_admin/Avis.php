<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
    <link rel="stylesheet" href="../css/Accueil.css">
    <link rel="stylesheet" href="../../Style/output.css">
</head>
<body>
    <?php session_start(); ?>
    <?php require_once(__DIR__.'/../CRUD/Avis/SelectAvis.php')?>
    <?php require_once(__DIR__.'/../CRUD/Locataires/CountLocataires.php')?>
    <?php require_once(__DIR__.'/../CRUD/Reservations/CountReservation.php')?>
    <?php require_once(__DIR__.'/../CRUD/Parcelles/CountParcelles.php')?>
    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>

    <div class="main-content">
    
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Commentaire(s)</h2>
        
        <?php if(isset($_SESSION['successDeleteAvis'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successDeleteAvis'];?>

            </p>
            <?php unset($_SESSION['successDeleteAvis']);?>
        <?php endif;?>
        
        <?php if(isset($_SESSION['erreurDeleteAvis'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurDeleteAvis'];?>

            </p>
            <?php unset($_SESSION['erreurDeleteAvis']);?>
        <?php endif;?>

        <table>
            <thead>
                <tr>
                    <th>Avis ID</th>
                    <th>Parc ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Avis</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($listeAvis as $avis) : ?>
                    <tr>
                        <td><?php echo $avis['Id_avis']; ?></td>
                        <td><?php echo $avis['Id_parc']; ?></td>
                        <td><?php echo $avis['Nom']; ?></td>
                        <td><?php echo $avis['Prenom']; ?></td>
                        <td><?php echo $avis['Email']; ?></td>
                        <td><?php echo $avis['Avis']; ?></td>
                        <td>
                            <form action="../CRUD/Avis/DeleteAvis.php" method="post">
                                
                                <input type="hidden" name="id_avis" value="<?php echo $avis['Id_avis']; ?>">

                                <button class="btn delete-btn" type="submit">Supprimer</button>

                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <script src="../Javascript/Accueil.js"></script>

</body>
</html>
