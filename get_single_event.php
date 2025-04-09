<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Only GET requests are allowed']);
    exit;
}

// Check if event_id is provided
if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing or invalid event_id']);
    exit;
}

$event_id = intval($_GET['event_id']);

// DB connection
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prepare and execute query
$stmt = $mysqli->prepare("SELECT * FROM Events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $event = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'event' => $event
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Event not found']);
}

$stmt->close();
$mysqli->close();
?>