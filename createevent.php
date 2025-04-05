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

// Validate required fields
$requiredFields = ['admin_id', 'role', 'university_id', 'name', 'category', 'description', 'event_time', 'location_name', 'contact_phone', 'contact_email', 'visibility'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing field: $field"]);
        exit;
    }
}

// Validate role
if ($data['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Only admins can create events']);
    exit;
}

// Validate ENUM fields
$allowedCategories = ['social', 'fundraising', 'tech_talk'];
$allowedVisibility = ['public', 'private', 'rso'];

if (!in_array($data['category'], $allowedCategories)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid event category']);
    exit;
}

if (!in_array($data['visibility'], $allowedVisibility)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid visibility']);
    exit;
}

// Parse and sanitize inputs
$admin_id = intval($data['admin_id']);
$university_id = intval($data['university_id']);
$rso_id = isset($data['rso_id']) ? intval($data['rso_id']) : null;

$name = trim($data['name']);
$category = $data['category'];
$description = trim($data['description']);
$event_time = $data['event_time']; // Format: "2025-04-15 14:00:00"
$location_name = trim($data['location_name']);
$contact_phone = trim($data['contact_phone']);
$contact_email = trim($data['contact_email']);
$visibility = $data['visibility'];

// DB connection
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prepare query
$stmt = $mysqli->prepare("
    INSERT INTO Events (rso_id, university_id, name, category, description, event_time, location_name, contact_phone, contact_email, visibility)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "iissssssss",
    $rso_id,
    $university_id,
    $name,
    $category,
    $description,
    $event_time,
    $location_name,
    $contact_phone,
    $contact_email,
    $visibility
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event created successfully', 'event_id' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Event creation failed', 'details' => $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>
