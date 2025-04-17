<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/Parcelle.css">
    <link rel="stylesheet" href="../css/ModalUpdateParcelle.css">
</head>
<body>

<?php session_start(); ?>
    <?php require_once(__DIR__.'/../CRUD/Parcelles/SelectParcelles.php')?>
    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>
    
    <div class="main-content">
    
    <?php require_once(__DIR__.'/../CRUD/Reservations/CountReservation.php')?>
    <?php require_once(__DIR__.'/../CRUD/Locataires/CountLocataires.php')?>
    <?php require_once(__DIR__.'/../CRUD/Parcelles/CountParcelles.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Parcelle(s)</h2>
        <table>
            <thead>
                <tr>
                    <th>Id p.</th>
                    <th>Id r.</th>
                    <th>Id u.</th>
                    <th>Taille p.</th>
                    <th>Nom p.</th>
                    <th>Prix p.</th>
                    <th>Status p.</th>
                    <th>Exposition</th>
                    <th>Equipements</th>
                    <th>Preferences</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $parcelles=$_SESSION['parcelles'];?>

                <?php foreach($parcelles as $parcelle) : ?>
                    <tr>
                        <td><?php echo $parcelle['Id_parc']; ?></td>
                        <td><?php echo $parcelle['Id_res']; ?></td>
                        <td><?php echo $parcelle['User_id']; ?></td>
                        <td><?php echo $parcelle['Taille_parc']; ?></td>
                        <td><?php echo $parcelle['Nom_parc']; ?></td>
                        <td><?php echo $parcelle['Prix_parc']; ?></td>
                        <td><?php echo $parcelle['Status_parc']; ?></td>
                        <td><?php echo $parcelle['Equipements']; ?></td>
                        <td><?php echo $parcelle['Exposition']; ?></td>
                        <td><?php echo $parcelle['Preferences']; ?></td>
                        <td><?php echo $parcelle['Description']; ?></td>
                        <td><?php echo $parcelle['Chemin_image']; ?></td>
                        <td>

                        <form action="../CRUD/Parcelles/SuppressionParcelles.php" method="post">

                            <input type="hidden" name="id_parc" value="<?php echo $parcelle['Id_parc']; ?>">
                            <button class="btn delete-btn" type="submit">Supprimer</button>

                        </form>
                        <form action="../CRUD/Parcelles/SelectPartielleParcelle.php" method="post">

                            <input type="hidden" name="id_parc" value="<?php echo $parcelle['Id_parc']; ?>">
                            <button class="btn edit-btn" type="submit">Modifier</button>

                        </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <td colspan="13">
                    <button class="add-btn" onclick="ouvrirModal('modal2')">
                        <i>+</i> Ajouter une parcelle
                    </button>
                </td>
            </tbody>
        </table>
    </div>
    </div>
    
    <div class="form-container" id="modal1">
        <div class="modal-content">

        <h1> MISE A JOUR PARCELLE </h1>
        <span class="fermer" onclick="fermerModal('modal1')">&times;</span>

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

        <form action="../CRUD/Parcelles/ModificationParcelles.php" method="post" enctype="multipart/form-data">
            
            <label for="id_parc">Id p.</label>
            <select id="id_parc" name="id_parc">
                <option><?php echo $_SESSION['my_id_parc'];?></option>
            </select>
        
        <?php $Uparcelles=$_SESSION['Uparcelles'];?>

        <?php foreach($Uparcelles as $Uparcelle):?>
            
            <label for="taille_parc">Taille p.</label>
            <input type="text" id="taille_parc" name="taille_parc" value="<?php echo trim($Uparcelle['Taille_parc']);?>" placeholder=".....">

            <label for="nom_parc">Nom p.</label>
            <input type="text" id="nom_parc" name="nom_parc" value="<?php echo trim($Uparcelle['Nom_parc']);?>" placeholder=".....">
            
            <label for="prix_parc">Prix p.</label>
            <input type="text" id="prix_parc" name="prix_parc" value="<?php echo trim($Uparcelle['Prix_parc']);?>" placeholder=".....">
            
            <label for="status_parc">Status p.</label>
            <input type="text" id="status_parc" name="status_parc" value="<?php echo trim($Uparcelle['Status_parc']);?>" placeholder=".....">

            <label for="equip">Equipements</label>
            <input type="text" id="equip" name="equip" value="<?php echo trim($Uparcelle['Equipements']);?>" placeholder=".....">  

            <label for="expo">Exposition</label>
            <input type="text" id="expo" name="expo" value="<?php echo trim($Uparcelle['Preferences']);?>" placeholder=".....">

            <label for="pref">Preferences</label>
            <input type="text" id="pref" name="pref" value="<?php echo trim($Uparcelle['Description']);?>" placeholder=".....">

            <label for="descrip">Description</label>
            <textarea id="descrip" name="descrip" rows="5" cols="65">
                <?php echo trim($Uparcelle['Description']); ?>
            </textarea>

            <!-- Champ pour uploader une nouvelle image -->
            <label for="file">Insérer une nouvelle image :</label>
            <input type="file" name="file" id="file" accept="image/*">
            
            <?php if(isset($_SESSION['erreurInsertPicture'])) :?>
                <p style="color: red; 
                            font-weight: bold; 
                            text-align: center;
                            position: relative;
                            top: 3px;">
                            
                            <?php echo $_SESSION['erreurInsertPicture'];?>

                </p>
                <?php unset($_SESSION['erreurInsertPicture']);?>
            <?php endif;?>

        <?php endforeach; ?>

            <button type="submit">Envoyer</button>
        </form>
        </div>
    </div>

    <div class="form-container" id="modal2">
        <div class="modal-content">

        <h1> AJOUT PARCELLE </h1>
        <span class="fermer" onclick="fermerModal('modal2')">&times;</span>
        
        <?php if(isset($_SESSION['erreurId'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurId'];?>

            </p>
            <?php unset($_SESSION['erreurId']);?>
        <?php endif;?>

        <?php if(isset($_SESSION['successCreate'])) :?>
            <p style="color: green; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['successCreate'];?>

            </p>
            <?php unset($_SESSION['successCreate']);?>
        <?php endif;?>

        <?php if(isset($_SESSION['erreurCreate'])) :?>
            <p style="color: red; 
                          font-weight: bold; 
                          text-align: center;
                          position: relative;
                          bottom: 10px;">
                          
                          <?php echo $_SESSION['erreurCreate'];?>

            </p>
            <?php unset($_SESSION['erreurCreate']);?>
        <?php endif;?>

        <form action="../CRUD/Parcelles/CreateParcelles.php" method="post" enctype="multipart/form-data">

            <label for="id_parc">Id p.</label>
            <input type="text" id="id_parc" name="id_parc" placeholder="....." required>
            
            <label for="taille_parc">Taille p.</label>
            <input type="text" id="taille_parc" name="taille_parc" placeholder="....." required>

            <label for="nom_parc">Nom p.</label>
            <input type="text" id="nom_parc" name="nom_parc" placeholder="....." required>
            
            <label for="prix_parc">Prix p.</label>
            <input type="text" id="prix_parc" name="prix_parc" placeholder="....." required>

            <label for="status_parc">Status p.</label>
            <input type="text" id="status_parc" name="status_parc" placeholder="....." required>

            <label for="equip">Equipements</label>
            <input type="text" id="equip" name="equip" placeholder="....." required>  

            <label for="expo">Exposition</label>
            <input type="text" id="expo" name="expo" placeholder="....." required>

            <label for="pref">Preferences</label>
            <input type="text" id="pref" name="pref" placeholder="....." required>

            <label for="descrip">Description</label>
            <textarea id="descrip" name="descrip" rows="5" cols="65" placeholder="....." required> </textarea>

            <label for="file">Insérer une image</label>
            <input type="file" name="file" id="file" accept="image/*" required>

            <?php if(isset($_SESSION['erreurInsertImage'])) :?>
                <p style="color: red; 
                            font-weight: bold; 
                            text-align: center;
                            position: relative;
                            top: 3px;">
                            
                            <?php echo $_SESSION['erreurInsertImage'];?>

                </p>
                <?php unset($_SESSION['erreurInsertImage']);?>
            <?php endif;?>

            <button type="submit">Ajouter</button>
        </form>
        </div>
    </div>

    <script src="../Javascript/Accueil.js"></script>

</body>
</html>