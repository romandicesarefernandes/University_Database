<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Only GET requests are allowed']);
    exit;
}

// Validate university_id parameter
if (!isset($_GET['university_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing university_id parameter']);
    exit;
}

$university_id = intval($_GET['university_id']);

// Database connection
$mysqli = new mysqli(
    "college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com",
    "admin",
    "PASSW0RD!ADM1N",
    "college_events_db"
);

if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Prepare and execute query
$query = "SELECT rso_id, name, description, status FROM RSOs WHERE university_id = ?";
$stmt = $mysqli->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to prepare statement']);
    exit;
}

$stmt->bind_param("i", $university_id);
$stmt->execute();
$result = $stmt->get_result();

$rsos = [];
while ($row = $result->fetch_assoc()) {
    $rsos[] = $row;
}

echo json_encode([
    'success' => true,
    'count' => count($rsos),
    'rsos' => $rsos
]);

$stmt->close();
$mysqli->close();
?>