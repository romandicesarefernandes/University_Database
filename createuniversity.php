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
// Database config
$host = 'college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com';
$db = 'college_events_db';
$user = 'admin';
$pass = 'PASSW0RD!ADM1N';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Get input (JSON format expected)
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate required fields
    if (
        empty($data['name']) ||
        empty($data['location'])
    ) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and location are required.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO Universities (name, location, description, num_students) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['location'],
        $data['description'] ?? null,
        $data['num_students'] ?? null
    ]);

    $newId = $pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'university_id' => $newId
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>