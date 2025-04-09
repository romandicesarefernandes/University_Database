<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
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

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// DB connection
$conn = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Validate input
$user_id = isset($data['user_id']) ? intval($data['user_id']) : 0;
$rso_id = isset($data['rso_id']) ? intval($data['rso_id']) : 0;

if ($user_id <= 0 || $rso_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user_id or rso_id']);
    exit;
}

// Check if already a member
$stmt = $conn->prepare("SELECT 1 FROM Students_RSOs WHERE user_id = ? AND rso_id = ?");
$stmt->bind_param('ii', $user_id, $rso_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'User already a member of this RSO']);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// Insert user
$insert = $conn->prepare("INSERT INTO Students_RSOs (user_id, rso_id) VALUES (?, ?)");
$insert->bind_param('ii', $user_id, $rso_id);

if ($insert->execute()) {
    echo json_encode(['success' => 'User successfully joined the RSO']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to join RSO']);
}
$insert->close();
$conn->close();
?>