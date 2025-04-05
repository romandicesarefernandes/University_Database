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

// Connect to DB
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
$event_id = $data['event_id'] ?? null;
$user_id = $data['user_id'] ?? null;
$comment = trim($data['comment'] ?? '');

if (!$event_id || !$user_id || empty($comment)) {
    http_response_code(400);
    echo json_encode([
        "error" => "Missing required fields",
        "debug" => [
            "event_id" => $event_id,
            "user_id" => $user_id,
            "comment" => $comment
        ]
    ]);
    exit;
}

// Insert into DB
$stmt = $mysqli->prepare("INSERT INTO Comments (event_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $event_id, $user_id, $comment);

if ($stmt->execute()) {
    echo json_encode([
        "message" => "Comment added successfully",
        "comment_id" => $stmt->insert_id,
        "event_id" => $event_id,
        "user_id" => $user_id,
        "comment" => $comment,
        "created_at" => date('Y-m-d H:i:s')
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to add comment"]);
}

$stmt->close();
$mysqli->close();
?>
