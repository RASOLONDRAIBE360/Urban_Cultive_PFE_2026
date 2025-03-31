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
      <form action="#" method="POST" class="reservation-form">
        
        <label for="nom">Nom complet</label>
        <input type="text" id="nom" name="nom" placeholder="Entrez votre nom complet" required>
        
        <label for="email">Adresse e-mail</label>
        <input type="email" id="email" name="email" placeholder="Entrez votre adresse e-mail" required>
        
        <label for="parcelle">Choisissez votre parcelle</label>
        <select id="parcelle" name="parcelle" required>
          <option value="parcelle1">Parcelle 1 - 100m²</option>
          <option value="parcelle2">Parcelle 2 - 150m²</option>
          <option value="parcelle3">Parcelle 3 - 200m²</option>
        </select>

        <label for="duree">Durée de la réservation (en mois)</label>
        <input type="number" id="duree" name="duree" placeholder="Durée de la réservation" min="1" required>

        <label for="date-debut">Date de début</label>
        <input type="date" id="date-debut" name="date-debut" required>

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
