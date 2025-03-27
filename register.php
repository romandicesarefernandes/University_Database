<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['email'], $data['password'], $data['role'], $data['university_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];
$role = $data['role'];
$university_id = intval($data['university_id']);

// Validate role
$allowed_roles = ['super_admin', 'admin', 'student'];
if (!in_array($role, $allowed_roles)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}

// Hash the password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// DB connection
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", 'admin', 'PASSW0RD!ADM1N', 'college_events_db');

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Check if email already exists
$stmt = $mysqli->prepare("SELECT user_id FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'User with this email already exists']);
    $stmt->close();
    exit;
}
$stmt->close();

// Insert new user
$stmt = $mysqli->prepare("INSERT INTO Users (university_id, email, password_hash, role) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $university_id, $email, $password_hash, $role);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'User registered successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'User registration failed']);
}

$stmt->close();
$mysqli->close();
?>