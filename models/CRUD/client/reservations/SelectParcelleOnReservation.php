<?php
require_once (__DIR__.'/../../../../config/MySQL.php');

$id_user = $_SESSION['user_id_user'];

$mysqlClient = new PDO(
    sprintf('mysql:host=%s;dbname=%s;port=%s;charset=utf8', MYSQL_HOST, MYSQL_NAME, MYSQL_PORT),
    MYSQL_USER,
    MYSQL_PASSWORD
);

$mysqlClient->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqlQueryToRecuperateReservation = "SELECT Id_parc
                        FROM reservation_parc
                        WHERE User_id = :id_user";

$dbRequestToRecuperateReservation = $mysqlClient->prepare($sqlQueryToRecuperateReservation);

$dbRequestToRecuperateReservation->execute([
    ':id_user' => $id_user
]);

$parcellesOnReservation = $dbRequestToRecuperateReservation->fetchAll(PDO::FETCH_ASSOC);
$_SESSION['parcellesOnReservation'] = $parcellesOnReservation;
?>