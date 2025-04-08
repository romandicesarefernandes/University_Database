<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: PUT, OPTIONS");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// DB connection
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$event_id = $data['event_id'] ?? null;

if (!$event_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing event_id"]);
    exit;
}

// Check if the current status is pending
$check_stmt = $mysqli->prepare("SELECT approval_status FROM Events WHERE event_id = ?");
$check_stmt->bind_param("i", $event_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["error" => "Event not found"]);
    exit;
}

$row = $result->fetch_assoc();
if ($row['approval_status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(["error" => "Event is not in pending status"]);
    exit;
}

// Update approval status to approved
$update_stmt = $mysqli->prepare("UPDATE Events SET approval_status = 'approved' WHERE event_id = ?");
$update_stmt->bind_param("i", $event_id);

if ($update_stmt->execute()) {
    echo json_encode([
        "message" => "Approval status updated to approved",
        "event_id" => $event_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update approval status"]);
}

$check_stmt->close();
$update_stmt->close();
$mysqli->close();
?>