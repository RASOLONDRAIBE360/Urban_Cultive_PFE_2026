<?php

session_start();

require_once (__DIR__.'/../../Config/MySQL.php');

unset($_SESSION['taille']);
unset($_SESSION['prix']);
unset($_SESSION['nom_parc']);

if (isset($_SESSION['nom_parc']) && isset($_SESSION['taille']) && isset($_SESSION['prix'])) {
    echo "<script>alert('Les variable de session est encore présent.');</script>";
} else {
    
    if(empty($_POST['nom_parc'])){
        echo "La variable nom_parc n'est pas défini";
    } else{
        $nom_parc = $_POST['nom_parc'];

            try {
                $mysqlClient = new PDO(
                        sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
                                MYSQL_USER,
                                MYSQL_PASSWORD
                                    );

                $mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sqlRequest = "SELECT * FROM info_parc WHERE Nom_parc = :nom_parc";
                $dtoStatement = $mysqlClient->prepare($sqlRequest);

                $dtoStatement->execute([
                    ':nom_parc'=>$_POST['nom_parc'],
                ]);
                
                $parcelles = $dtoStatement->fetchAll(PDO::FETCH_ASSOC);

                foreach($parcelles as $parcelle){
                    $_SESSION['taille'] = $parcelle['Taille_parc'];
                    $_SESSION['prix'] = $parcelle['Prix_parc'];
                    $_SESSION['nom_parc'] = $parcelle['Nom_parc'];
                };

                echo '<script>window.location.href="../GestionReservation/Reservation.php";</script>';
                

            } catch (Exception $exception) {
                die('Erreur : ' . $exception->getMessage());
            }
        }
    }

?>