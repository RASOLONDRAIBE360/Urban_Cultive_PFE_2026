<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../css/Index.css">
    <link rel="stylesheet" href="../css/Modal_update.css">
</head>
<body>
    <?php require_once(__DIR__.'/../Navigation/Navigation.php')?>

    <div class="main-content">
    
    <?php require_once(__DIR__.'/../CRUD/Reservation.php')?>
    <?php require_once(__DIR__.'/../CRUD/Locataires.php')?>
    <?php require_once(__DIR__.'/../Header/Header.php')?>

    <div class="recent-orders">
        <h2>Liste Reservation</h2>
        <table>
            <thead>
                <tr>
                    <th>Id r.</th>
                    <th>Email</th>
                    <th>N° tel</th>
                    <th>Nom p.</th>
                    <th>Prix p.</th>
                    <th>Date r.</th>
                    <th>Duree r.</th>
                    <th>Status r.</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reservations as $reservation) : ?>
                    <tr>
                        <td><?php echo $reservation['Id_res']; ?></td>
                        <td><?php echo $reservation['Email']; ?></td>
                        <td><?php echo $reservation['Numero_tel']; ?></td>
                        <td><?php echo $reservation['Nom_parc']; ?></td>
                        <td><?php echo $reservation['Prix_parc']; ?></td>
                        <td><?php echo $reservation['Date_res']; ?></td>
                        <td><?php echo $reservation['Duree_res']; ?></td>
                        <td><?php echo $reservation['Status_res']; ?></td>
                        <td>

                        <form action="../CRUD/Validation.php" method="post">

                            <input type="hidden" name="id_res" value="<?php echo $reservation['Id_res'];?>">
                            <button class="btn valid-btn" type="submit">VALIDER</button>

                        </form>

                        <form action="../CRUD/Refus.php" method="post">

                            <input type="hidden" name="id_res" value="<?php echo $reservation['Id_res']; ?>">
                            <button class="btn delete-btn" type="submit">REFUSER</button>

                        </form>

                        <form action="../CRUD/Attente.php" method="post">

                            <input type="hidden" name="id_res" value="<?php echo $reservation['Id_res']; ?>">
                            <button class="btn edit-btn" type="submit">MISE EN ATTENTE</button>

                        </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div class="form-container" id="modal1">
        <div class="modal-content">

        <h1> VALIDATION RESERVATION </h1>
        <span class="fermer" onclick="fermerModal('modal1')">&times;</span>

        <form action="../CRUD/Mail.php" method="post">

            <label for="dest">Destinataire</label>
            <input type="email" id="dest" name="dest" required>

            <label for="objet">Objet</label>
            <input type="text" id="objet" name="objet" required>

            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>

            <button type="submit">Envoyer</button>
        </form>
        </div>
    </div>
    
    <script src="../Javascript/Index.js"></script>
</body>
</html>