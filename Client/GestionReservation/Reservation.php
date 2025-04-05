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

  <?php require_once(__DIR__.'/../CRUD/Select.php')?>

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
      <form action="../CRUD/Enregistrement.php" method="post" class="reservation-form">
        
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" placeholder="Entrez votre nom" required>

        <label for="prenom">Prenom</label>
        <input type="text" id="prenom" name="prenom" placeholder="Entrez votre prenom" required>
        
        <label for="email">Adresse e-mail</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre adresse e-mail" required>

        <label for="tel">Numero telephone</label>
        <input type="text" id="tel" name="tel" placeholder="Entrer votre numero de telephone" required>

        <label for="nom_parc">Nom parcelle</label>
        <input type="text" id="nom_parc" name="nom_parc" value=<?php echo $_SESSION['nom_parc'];?> disabled>
        
        <label for="prix">Prix parcelle</label>
        <input type="text" id="prix" name="prix" value=<?php echo $_SESSION['prix'];?> disabled>

        <label for="taille">Taille parcelle</label>
        <input type="text" id="taille" name="taille" value="<?php echo $_SESSION['taille'];?>" disabled>

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
