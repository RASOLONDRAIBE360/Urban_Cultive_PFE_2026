<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jardins Urbains</title>
    <link rel="stylesheet" href="css/Accueil.css">
</head>
<body>

    <?php session_start();?>

    <?php require_once(__DIR__.'/Header/Header.php')?>

    <section class="hero">
        <?php foreach($users as $user) :?>
            <h1><?php echo "Bienvenue sur mon site {$_SESSION['Nom']} {$_SESSION['Prenom']}";?></h1>
        <?php endforeach;?>
        <h1>Trouvez votre <span style="color:#2d6a4f">parcelle idéale</span></h1>
        <p>Explorez notre sélection de parcelles urbaines et donnez vie à votre projet de jardinage</p>
    </section>
    <section class="parcelles">
        <div class="card" onclick="ouvrirModal('modal1')">
            <img src="Projet_HTML_CSS/Photo1.jpg" alt="Parcelle Zen">
            <div class="card-content">
                <h3>Parcelle Zen</h3>
                <p class="disponible">Disponible</p>
                <p>120m² - Plein soleil - Point d'eau</p>
                <p>180€/mois</p>
            </div>
        </div>
        <div class="card" onclick="ouvrirModal('modal2')">
            <img src="Projet_HTML_CSS/Photo2.jpg" alt="L'Oasis Verte">
            <div class="card-content">
                <h3>L'Oasis Verte</h3>
                <p class="occupe">Occupé</p>
                <p>85m² - Mi-ombre - Point d'eau</p>
                <p>150€/mois</p>
            </div>
        </div>
        <div class="card" onclick="ouvrirModal('modal3')">
            <img src="Projet_HTML_CSS/Photo3.jpg" alt="Le Carré Fertile">
            <div class="card-content">
                <h3>Le Carré Fertile</h3>
                <p class="disponible">Disponible</p>
                <p>150m² - Plein soleil - Point d'eau</p>
                <p>220€/mois</p>
            </div>
        </div>
    </section>

    <!-- Modals --> 
<div id="modal1" class="modal">
    <div class="modal-content">
        <span class="fermer" onclick="fermerModal('modal1')">&times;</span>
        <h2>🌿 Parcelle Zen</h2>
        <img src="Projet_HTML_CSS/Photo1.jpg" alt="Parcelle Zen" style="width: 100%; border-radius: 8px;">
        <p><strong>Surface :</strong> 120m²</p>
        <p><strong>Exposition :</strong> Plein soleil</p>
        <p><strong>Équipements :</strong> Point d'eau à proximité</p>
        <p><strong>Idéal pour :</strong> Cultures potagères, arbres fruitiers, espace détente</p>
        <p><strong>Prix :</strong> 180€/mois</p>
        <p>Une parcelle parfaite pour ceux qui recherchent un espace ensoleillé et paisible pour cultiver leurs plantes ou aménager un petit coin de nature.</p>

        <button class="avis-btn" onclick='window.location.href=""'> Voir les avis</button>
        <button class="reservation-btn" onclick='window.location.href="GestionReservation/Reservation.php"'>Réserver cette parcelle</button>
    </div>
</div>

<div id="modal2" class="modal">
    <div class="modal-content">
        <span class="fermer" onclick="fermerModal('modal2')">&times;</span>
        <h2>🌱 L'Oasis Verte</h2>
        <img src="Projet_HTML_CSS/Photo2.jpg" alt="L'Oasis Verte" style="width: 100%; border-radius: 8px;">
        <p><strong>Surface :</strong> 85m²</p>
        <p><strong>Exposition :</strong> Mi-ombre</p>
        <p><strong>Équipements :</strong> Accès à l’eau, sol fertile</p>
        <p><strong>Idéal pour :</strong> Culture maraîchère, plantes médicinales, jardin botanique</p>
        <p><strong>Prix :</strong> 150€/mois</p>
        <p>Un espace équilibré entre ombre et lumière, idéal pour des plantes nécessitant un climat modéré. Parfait pour un jardin médicinal ou une petite exploitation.</p>

        <button class="avis-btn" onclick='window.location.href=""'> Voir les avis</button>
        <button class="reservation-btn" onclick='window.location.href="GestionReservation/Reservation.php"'>Réserver cette parcelle</button>
    </div>
</div>

<div id="modal3" class="modal">
    <div class="modal-content">
        <span class="fermer" onclick="fermerModal('modal3')">&times;</span>
        <h2>🌾 Le Carré Fertile</h2>
        <img src="Projet_HTML_CSS/Photo3.jpg" alt="Le Carré Fertile" style="width: 100%; border-radius: 8px;">
        <p><strong>Surface :</strong> 150m²</p>
        <p><strong>Exposition :</strong> Plein soleil</p>
        <p><strong>Équipements :</strong> Point d'eau et sol enrichi</p>
        <p><strong>Idéal pour :</strong> Cultures intensives, serres, vergers</p>
        <p><strong>Prix :</strong> 220€/mois</p>
        <p>Une parcelle généreuse avec un sol préparé pour une agriculture durable. Idéale pour ceux qui veulent produire en quantité ou expérimenter des cultures variées.</p>

        <button class="avis-btn" onclick='window.location.href=""'> Voir les avis</button>
        <button class="reservation-btn" onclick='window.location.href="GestionReservation/Reservation.php"'>Réserver cette parcelle</button>
    </div>
</div>

<script src="Javascript/Accueil.js"></script>
</body>
</html>
