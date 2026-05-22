<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "maison_ESP32";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

// Dernières données
$derniere = $conn->query("SELECT * FROM donnees ORDER BY id DESC LIMIT 1")->fetch_assoc();

// Historique pour les graphes
$historique = $conn->query("SELECT * FROM donnees ORDER BY timestamp DESC LIMIT 50");
?>