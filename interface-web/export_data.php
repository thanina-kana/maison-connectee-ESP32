<?php
include 'bdd.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT * FROM donnees ORDER BY timestamp ASC");
$rows = [];
while($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
echo json_encode($rows);
?>