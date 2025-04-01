<?php
// Set the response content type to JSON
header("Content-Type: application/json");

// Enable error reporting for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable detailed MySQLi error reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Load database configuration from external config file
$config = require __DIR__ . '/config.php';

// Select the correct config section for the "aktivnosti" database
$db = $config['aktivnosti'];

// Connect to the MySQL database using MySQLi
$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);

// Set the connection charset to UTF-8
$conn->set_charset("utf8");

// Read incoming JSON data (for POST requests)
$input = json_decode(file_get_contents("php://input"), true);

// Determine the action (e.g., dodaj, izbrisi) from query string
$action = $_GET['action'] ?? '';

// =====================
// GET request: Return all activities
// =====================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$action) {
    $result = $conn->query("SELECT * FROM aktivnosti ORDER BY id DESC");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rows);
    exit;
}

// =====================
// POST request: Add a new activity
// =====================
if ($action === 'dodaj' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($input['aktivnost']) || empty($input['znanje']) || empty($input['cas'])) {
        http_response_code(400);
        echo json_encode(["error" => "Izpolni vsa polja (aktivnost, znanje, cas)!"]);
        exit;
    }

    // Prepare SQL insert statement and bind parameters
    $stmt = $conn->prepare("INSERT INTO aktivnosti (aktivnost, znanje, cas) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $input['aktivnost'], $input['znanje'], $input['cas']);
    $stmt->execute();

    // Return success message
    echo json_encode(["success" => "Aktivnost dodana."]);
    exit;
}

// =====================
// POST request: Delete activity by ID
// =====================
if ($action === 'izbrisi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if 'id' was provided
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Manjka 'id' za brisanje."]);
        exit;
    }

    // Prepare SQL delete statement and bind ID
    $stmt = $conn->prepare("DELETE FROM aktivnosti WHERE id = ?");
    $stmt->bind_param("i", $input['id']);
    $stmt->execute();

    // Return success message
    echo json_encode(["success" => "Aktivnost izbrisana."]);
    exit;
}

// =====================
// Default fallback: Invalid method or action
// =====================
http_response_code(400);
echo json_encode(["error" => "Neveljavna zahteva ali metoda."]);
