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
    echo json_encode(['error' => 'Only PUT requests are allowed']);
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
$comment_id = $data['comment_id'] ?? null;
$user_id = $data['user_id'] ?? null;
$new_comment = trim($data['comment'] ?? '');

if (!$comment_id || !$user_id || empty($new_comment)) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// Check if comment belongs to user
$check_stmt = $mysqli->prepare("SELECT * FROM Comments WHERE comment_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $comment_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "You are not authorized to edit this comment"]);
    exit;
}

// Update comment
$update_stmt = $mysqli->prepare("UPDATE Comments SET comment = ? WHERE comment_id = ?");

$update_stmt->bind_param("si", $new_comment, $comment_id);

if ($update_stmt->execute()) {
    echo json_encode([
        "message" => "Comment updated successfully",
        "comment_id" => $comment_id,
        "updated_comment" => $new_comment
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update comment"]);
}

$check_stmt->close();
$update_stmt->close();
$mysqli->close();
?>
