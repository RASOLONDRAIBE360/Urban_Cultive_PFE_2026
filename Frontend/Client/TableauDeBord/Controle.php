<?php session_start();

/* Récupération de l'ID dont le cas où l'utilisateur 
a tenté de supprimer le donné au niveau de l'URL */

if (isset($_GET['id_parc'])) {
    $id_parc = htmlspecialchars($_GET['id_parc']);
    // Traitement ici
} else {
    echo "ID de parcelle manquant.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../../Style/output.css">
    <title>Tableau de bord</title>
</head>
<body>
    
    <!--En-tête de la page-->
    <header class="bg-white w-full px-6 py-4 shadow-md">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between gap-2">
    
    <h1 class="font-bold text-[30px] text-gray-800">
      Système d'Arrosage Intelligent
    </h1>
    <p>
        <?php echo $id_parc ? "Parcelle " . $id_parc : "L'id du parcelle est non spécifié !"; ?>
    </p>
    
    <div class="text-gray-600 text-base">
      <p class="text-sm text-gray-500">Dernière mise à jour :</p>
    </div>

  </div>
</header>

    <section class="bg-green-50 p-6">
        <!--Partie aperçue des données renvoyés par le capteur-->
        <section class="grid grid-cols-2 md:grid-cols-4 gap-8 my-6">
    
            <div class="bg-white rounded-lg border border-gray-300 p-6 shadow-sm hover:shadow-md transition">
                <h1 class="text-xl font-semibold text-gray-800">🌱 Humidité du sol</h1>
            </div>

            <div class="bg-white rounded-lg border border-gray-300 p-6 shadow-sm hover:shadow-md transition">
                <h1 class="text-xl font-semibold text-gray-800">🌡️ Température</h1>
            </div>

            <div class="bg-white rounded-lg border border-gray-300 p-6 shadow-sm hover:shadow-md transition">
                <h1 class="text-xl font-semibold text-gray-800">💧 Humidité Air</h1>
            </div>

            <div id="data_luminosity" class="bg-white rounded-lg border border-gray-300 p-6 shadow-sm hover:shadow-md transition">
                <h1 class="text-xl font-semibold text-gray-800">☀️ Luminosité</h1>
            </div>

        </section>

        <!--Partie commande pour : activer / désactiver / planifier / modifier mode arrosage-->
        <section class="border rounded-lg px-[30px] py-2 bg-white grid grid-cols-1 md:grid-cols-2 gap-8 my-6">
            <!--Affichage du mode activé-->
            <div>
                <h1 class="font-bold text-[25px]"> Contrôle d'Arrosage </h1>
                <div>
                    <p> Système en veille </p>
                    <p> Mode : </p>
                </div>
            </div>

            <!--Contrôle du mode d'arrosage manuel-->
            <div>
                <h2 class="font-semibold text-[20px]"> Contrôle Manuel </h2>
                <button class="bg-blue-500 text-white px-4 py-2 rounded mr-2">Démarrer l'arrosage</button>
                <button class="bg-red-500 text-white px-4 py-2 rounded">Arrêter l'arrosage</button>
            </div>

            <!--Contrôle du mode d'arrosage automatique-->
            <div>
                <h2 class="font-semibold text-[20px]"> Mode Automatique </h2>
                <div>
                    <p> Arrosage automatique basé sur les capteurs </p>
                    <p> Se déclenche quand l'humidité du sol < 30%</p>
                    <button id="toggleBtn" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors bg-gray-300">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1"></span>
                    </button>
                </div>
            </div>

            <!--Programmation heure d'arrosage-->
            <div>
                <h2 class="font-semibold text-[20px]"> Programmation </h2>
                <div>
                    <label for="arrosage-time" class="block mb-2">Heure</label>
                    <input type="time" id="arrosage-time" name="arrosage-time" class="border border-gray-300 rounded px-3 py-2">

                    <label for="duree" class="block mb-2 mt-4">Duree (min)</label>
                    <input type="number" id="duree" name="duree" min="0" max="60" step="1" value="0" class="border border-gray-300 rounded px-[10px] py-2">

                    <button class="bg-green-500 text-white px-4 py-2 rounded ml-2">Programmer</button>
                </div>
            </div>
        </section>

        <!--Partie Menu-->
        <section>
            <!--Affichage de la recommandation d'arrosage à faire-->
            <div>
                <h1 class="font-bold text-[25px]"> Recommandation d'Arrosage </h1>
                <div>
                    <p> Aucune recommandation pour le moment. </p>
                </div>
            </div>
        </section>

        <h1>Contrôle de la LED</h1>
        <button onclick="fetch('http://localhost:3000/led/on')">Allumer</button>
        <button onclick="fetch('http://localhost:3000/led/off')">Éteindre</button>
    </section>
    
    <script src="../Javascript/WebSocketData.js"></script>
    <script src="../Javascript/Controle.js"></script>
</body>
</html>