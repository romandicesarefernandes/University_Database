<?php
header('Content-Type: application/json');

$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

if ($mysqli->connect_errno) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

$event_id = $_GET['event_id'] ?? null;

if (!$event_id) {
    http_response_code(400);
    echo json_encode(["error" => "Missing event_id"]);
    exit;
}

// Optional: join with user table if you want to show usernames
$query = "
    SELECT c.comment_id, c.user_id, u.email, c.comment, c.created_at
    FROM Comments c
    JOIN Users u ON c.user_id = u.user_id
    WHERE c.event_id = ?
    ORDER BY c.created_at DESC
";

$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();

$result = $stmt->get_result();
$comments = [];

while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);

$stmt->close();
$mysqli->close();
?>