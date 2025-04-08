<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['action'])) {
        $action = $_GET['action'];

        if ($action === 'dodaj') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $stevilo = $data['stevilo'] ?? 0;
            $kraj = $data['kraj'] ?? '';

            if (!$kraj || $stevilo <= 0) {
                echo json_encode(["error" => "Neveljavni podatki."]);
                exit;
            }

            // Samodejno vstavimo trenutni datum/uro z NOW()
            $stmt = $pdo->prepare("INSERT INTO promet_avtomobilov (datum_zajema, stevilo, kraj) VALUES (NOW(), ?, ?)");
            $stmt->execute([$stevilo, $kraj]);

            echo json_encode(["success" => "Zapis dodan."]);
            exit;
        }

        if ($action === 'izbrisi') {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $id = $data['id'] ?? 0;

            $stmt = $pdo->prepare("DELETE FROM promet_avtomobilov WHERE stevilka = ?");
            $stmt->execute([$id]);

            echo json_encode(["success" => "Zapis izbrisan."]);
            exit;
        }
    }

    $stmt = $pdo->query("SELECT * FROM promet_avtomobilov ORDER BY datum_zajema DESC");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo json_encode(["error" => "Napaka: " . $e->getMessage()]);
}
