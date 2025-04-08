<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

$host = 'college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com';
$db = 'college_events_db';
$user = 'admin';
$pass = 'PASSW0RD!ADM1N';

$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

$university_id = $data['university_id'] ?? null;
$name = $data['name'] ?? null;
$description = $data['description'] ?? null;
$admin_id = $data['admin_id'] ?? 1; // Default admin_id = 1

// Validate required fields
if (!$university_id || !$name || !$admin_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO RSOs (university_id, name, description, admin_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("issi", $university_id, $name, $description, $admin_id);

// Execute and respond
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'RSO created successfully', 'rso_id' => $stmt->insert_id]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>