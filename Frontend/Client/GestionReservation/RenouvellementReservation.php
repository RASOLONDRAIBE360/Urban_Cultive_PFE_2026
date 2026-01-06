<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Réservation de Parcelle Urbaine</title>
  <link rel="stylesheet" href="../css/Reservation.css">
</head>
<body>

  <?php session_start();?>
  <button class="avis-btn" onclick="window.location.href='../Site_web_user/Parcelle.php?showModal=<?php echo $_SESSION['id_parc'];?>'">Retour</button>

  <!-- En-tête -->
  <header>
    <div class="container">
      <h1>Réservez votre Parcelle Urbaine</h1>
      <p>Une gestion sur mesure pour vos besoins agricoles ou botaniques</p>
    </div>
  </header>

  <!-- Formulaire de réservation -->
  <section class="reservation-section">
    <div class="container">
        <h2>Réservez maintenant</h2>
      
        <?php if(isset($_SESSION['erreurRenouvellement'])) :?>
            <p style="color: red; 
                        font-weight: bold; 
                        text-align: center;
                        position: relative;
                        bottom: 10px;">
                        
                        <?php echo $_SESSION['erreurRenouvellement'];?>

            </p>
            <?php unset($_SESSION['erreurRenouvellement']);?>
        <?php endif;?>

        <form action="../CRUD/Reservations/UpdateRenouvellementReservation.php" method="post" class="reservation-form">
                
            <?php $ThisParcelles = $_SESSION['ThisParcelles'];?>

            <?php foreach($ThisParcelles as $ThisParcelle):?>

            <label for="id_parc">Id parcelle</label>
            <input type="text" id="id_parc" name="id_parc" value="<?php echo $_SESSION['id_parc'];?>" readonly style=" color: #5e5e5e; background: #fafafa; cursor: default; border-color: #ccc; caret-color: transparent;" >

            <label for="nom_parc">Nom parcelle</label>
            <input type="text" id="nom_parc" name="nom_parc" value="<?php echo $ThisParcelle['Nom_parc'];?>" disabled style="caret-color: transparent;">

            <label for="prix">Prix parcelle</label>
            <input type="text" id="prix" name="prix" value=<?php echo $ThisParcelle['Prix_parc'];?> disabled style="caret-color: transparent;">

            <label for="taille">Taille parcelle</label>
            <input type="text" id="taille" name="taille" value="<?php echo $ThisParcelle['Taille_parc'];?>" disabled style="caret-color: transparent;">

            <label for="duree">Durée de la réservation (en mois)</label>
            <input type="number" id="duree" name="duree" value="<?php echo $ThisParcelle['Duree_res'];?>" min="1" required>

            <label for="date_debut">Date de début</label>
            <input type="date" id="date_debut" name="date_debut" required>

            <?php endforeach;?>

            <button type="submit" class="btn">Renouveller</button>
        </form>
    </div>
  </section>

   <!-- Pied de page -->
  <footer>
    <div class="container">
      <p>&copy; 2025 Gestion des Parcelles Urbaines - Tous droits réservés</p>
    </div>
  </footer>

</body>
</html>
