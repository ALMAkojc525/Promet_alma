<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header("Content-Type: application/json");

// Povezava na bazo
$config = require __DIR__ . '/config.php';
$db = $config['avtomobili'];
$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);
$conn->set_charset("utf8");

// DODAJANJE ZAPISA
if (isset($_GET['action']) && $_GET['action'] === 'dodaj') {
    $data = json_decode(file_get_contents("php://input"), true);

    // ➕ Debug log za preverjanje vhodnih podatkov (Lambda, JS itd.)
    error_log("DODAJ zahteva: " . json_encode($data));

    if (isset($data['stevilo']) && isset($data['kraj'])) {
        $stevilo = $data['stevilo'];
        $kraj = $data['kraj'];

        if (!$kraj || $stevilo <= 0) {
            echo json_encode(["error" => "Neveljavni podatki."]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO prehod_avtomobilov (datum_zig, stevilo, kraj) VALUES (NOW(), ?, ?)");
        $stmt->bind_param("is", $stevilo, $kraj);
        $stmt->execute();

        echo json_encode(["success" => "Zapis uspešno dodan."]);
        exit;
    } else {
        echo json_encode(["error" => "Manjkajoči podatki."]);
        exit;
    }
}

// BRISANJE ZAPISA
if (isset($_GET['action']) && $_GET['action'] === 'izbrisi') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? 0;

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM prehod_avtomobilov WHERE stevilka = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["success" => "Zapis uspešno izbrisan."]);
    } else {
        echo json_encode(["error" => "Neveljaven ID."]);
    }
    exit;
}

// PRIKAZ VSEH ZAPISOV
$sql = "SELECT stevilka, datum_zig AS datum_zajema, stevilo, kraj FROM prehod_avtomobilov ORDER BY datum_zig DESC";
$result = $conn->query($sql);
$rows = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($rows);
