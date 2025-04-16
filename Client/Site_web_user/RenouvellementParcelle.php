<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Renouvellement Réservation</title>
  <link rel="stylesheet" href="../css/Reservation.css">
</head>
<body>
  
  <?php session_start();?>

  <button class="avis-btn" onclick='window.location.href="../Accueil.php?showModal=1"'>Retour</button>

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
      <form action="../CRUD/Reservations/CreateReservation.php" method="post" class="reservation-form">
        
        <label for="user_id">ID Utilisateur</label>
        <select id="user_id" name="user_id">
            <option><?php echo $_SESSION['user_id'];?></option>
        </select>
      
        <label for="tel">Numero telephone</label>
        <input type="text" id="tel" name="tel" placeholder="Entrer votre numero de telephone" required>

        <?php $Myparcelles = $_SESSION['MyParcelles'];?>

        <?php foreach($Myparcelles as $MyParcelle) :?>

          <label for="id_parc">Id parcelle</label>
          <input type="text" id="id_parc" name="id_parc" value="<?php echo $_SESSION['id_parc'];?>" readonly style=" color: #5e5e5e; background: #fafafa;" >

          <label for="nom_parc">Nom parcelle</label>
          <input type="text" id="nom_parc" name="nom_parc" value="<?php echo $MyParcelle['Nom_parc'];?>" disabled>

          <label for="prix">Prix parcelle</label>
          <input type="text" id="prix" name="prix" value=<?php echo $MyParcelle['Prix_parc'];?> disabled>

          <label for="taille">Taille parcelle</label>
          <input type="text" id="taille" name="taille" value="<?php echo $MyParcelle['Taille_parc'];?>" disabled>

        <?php endforeach;?>

        <label for="duree">Durée de la réservation (en mois)</label>
        <input type="number" id="duree" name="duree" placeholder="Durée de la réservation" min="1" required>

        <label for="date_debut">Date de début</label>
        <input type="date" id="date_debut" name="date_debut" required>

        <button type="submit" class="btn">Réserver la parcelle</button>
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
