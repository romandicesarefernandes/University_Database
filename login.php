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

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['email'], $data['password'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing email or password']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

// DB connection
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch user
$stmt = $mysqli->prepare("SELECT user_id, password_hash, role, university_id FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    $stmt->close();
    exit;
}

$stmt->bind_result($user_id, $stored_hash, $db_role, $db_university_id);
$stmt->fetch();

// Verify password
if (!password_verify($password, $stored_hash)) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid email or password']);
    $stmt->close();
    exit;
}

// Success
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'user_id' => $user_id,
    'email' => $email,
    'role' => $db_role,
    'university_id' => $db_university_id
]);

$stmt->close();
$mysqli->close();
?>
