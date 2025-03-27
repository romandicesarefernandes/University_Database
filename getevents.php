<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Only GET requests are allowed']);
    exit;
}

// DB connection
$mysqli = new mysqli("college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com", "admin", "PASSW0RD!ADM1N", "college_events_db");

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch all events
$query = "SELECT * FROM Events ORDER BY event_time ASC";
$result = $mysqli->query($query);

$events = [];

if ($result) {
    //if ($result->num_rows === 0) {
    //    echo json_encode(['success' => true, 'count' => 0, 'events' => []]);
    //    exit;
    //  }
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    echo json_encode([
        'success' => true,
        'count' => count($events),
        'events' => $events
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch events']);
}

$mysqli->close();
?>