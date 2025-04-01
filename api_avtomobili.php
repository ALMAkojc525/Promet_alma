<?php
// Prikaz napak za diagnostiko
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header("Content-Type: application/json");

// Povezava na bazo preko config.php
$config = require __DIR__ . '/config.php';
$db = $config['avtomobili'];

// Ustvari povezavo
$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);
$conn->set_charset("utf8");

// SQL poizvedba
$sql = "SELECT * FROM prehod_avtomobilov ORDER BY datum_zajema DESC";
$result = $conn->query($sql);

// Pretvori v asociativno polje
$rows = $result->fetch_all(MYSQLI_ASSOC);

// JSON odgovor
echo json_encode($rows);
