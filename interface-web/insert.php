<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "maison_ESP32";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur connexion : " . $conn->connect_error);
}

$temperature = floatval($_GET['temperature']);
$humidite = floatval($_GET['humidity']);
$obstacle = intval($_GET['obstacle']);
$flamme = intval($_GET['flamme']);

$stmt = $conn->prepare("INSERT INTO donnees (temperature, humidite, obstacle, flamme) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ddii", $temperature, $humidite, $obstacle, $flamme);

if ($stmt->execute()) {
    echo "OK";
} else {
    echo "Erreur : " . $conn->error;
}

$stmt->close();
$conn->close();
?>