<?php
// Set response content type to JSON
header("Content-Type: application/json");

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Enable detailed error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Load configuration file containing DB credentials
$config = require __DIR__ . '/config.php';

// Select the database configuration for 'mercator'
$db = $config['mercator'];

// Establish a connection to the MySQL database
$conn = new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);

// Set character set to UTF-8 for proper encoding
$conn->set_charset("utf8");

// Parse incoming JSON request body (for POST requests)
$input = json_decode(file_get_contents("php://input"), true);

// Read action from the query string (e.g., ?action=dodaj)
$action = $_GET['action'] ?? '';

/**
 * GET request with no action specified:
 * Return all items in the 'artikel' table, ordered by descending 'stevilka'
 */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$action) {
    $result = $conn->query("SELECT * FROM artikel ORDER BY stevilka DESC");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rows);
    exit;
}

/**
 * GET request with action=total:
 * Return the total sum of all 'cena' values in the 'artikel' table
 */
if ($action === 'total' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $conn->query("SELECT SUM(cena) AS total FROM artikel");
    $row = $result->fetch_assoc();
    echo json_encode(["total" => $row['total'] ?? 0]);
    exit;
}

/**
 * POST request with action=dodaj:
 * Add a new item to the 'artikel' table
 */
if ($action === 'dodaj' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input fields
    if (empty($input['naziv']) || !isset($input['cena'])) {
        http_response_code(400);
        echo json_encode(["error" => "Manjka naziv ali cena."]);
        exit;
    }

    // Prepare and execute SQL insert statement
    $stmt = $conn->prepare("INSERT INTO artikel (naziv, cena) VALUES (?, ?)");
    $stmt->bind_param("sd", $input['naziv'], $input['cena']);
    $stmt->execute();

    echo json_encode(["success" => "Artikel dodan."]);
    exit;
}

/**
 * POST request with action=izbrisi:
 * Delete an item from the 'artikel' table by its 'stevilka'
 */
if ($action === 'izbrisi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate presence of 'id' field
    if (!isset($input['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Manjka id."]);
        exit;
    }

    // Prepare and execute SQL delete statement
    $stmt = $conn->prepare("DELETE FROM artikel WHERE stevilka = ?");
    $stmt->bind_param("i", $input['id']);
    $stmt->execute();

    echo json_encode(["success" => "Artikel izbrisan."]);
    exit;
}

// If none of the above conditions matched, return a generic error
http_response_code(400);
echo json_encode(["error" => "Neveljaven zahtevek."]);
