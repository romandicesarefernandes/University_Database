<?php
header('Content-Type: application/json');

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

if (!$comment_id || !$user_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

// Verify comment belongs to user
$check_stmt = $mysqli->prepare("SELECT * FROM Comments WHERE comment_id = ? AND user_id = ?");
$check_stmt->bind_param("ii", $comment_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "You are not authorized to delete this comment"]);
    exit;
}

// Proceed to delete
$delete_stmt = $mysqli->prepare("DELETE FROM Comments WHERE comment_id = ?");
$delete_stmt->bind_param("i", $comment_id);

if ($delete_stmt->execute()) {
    echo json_encode(["message" => "Comment deleted successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to delete comment"]);
}

$check_stmt->close();
$delete_stmt->close();
$mysqli->close();
?>