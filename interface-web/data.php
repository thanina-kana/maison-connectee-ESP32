<?php
include 'bdd.php';
header('Content-Type: application/json');

$derniere = $conn->query("SELECT * FROM donnees ORDER BY id DESC LIMIT 1")->fetch_assoc();

$historique = [];
$result = $conn->query("SELECT * FROM donnees ORDER BY timestamp DESC LIMIT 20");
while($row = $result->fetch_assoc()) {
    $historique[] = $row;
}

$presence = [];
$result2 = $conn->query("SELECT * FROM donnees WHERE obstacle = 1 ORDER BY timestamp DESC");
while($row = $result2->fetch_assoc()) {
    $presence[] = $row;
}

$flammes = [];
$result3 = $conn->query("SELECT * FROM donnees WHERE flamme = 1 ORDER BY timestamp DESC");
while($row = $result3->fetch_assoc()) {
    $flammes[] = $row;
}

echo json_encode([
    'temperature' => $derniere['temperature'],
    'humidite'    => $derniere['humidite'],
    'obstacle'    => $derniere['obstacle'],
    'flamme'      => $derniere['flamme'],
    'timestamp'   => $derniere['timestamp'],
    'historique'  => $historique,
    'presence'    => $presence,
    'flammes'     => $flammes
]);
?>