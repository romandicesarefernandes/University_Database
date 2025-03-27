<?php
$host = 'college-events-db.cjg48kowcw88.us-east-2.rds.amazonaws.com';
$port = 3306;
$dbname = 'college_events_db';
$username = 'admin';
$password = 'PASSW0RD!ADM1N';
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);

    // Optional: Set error reporting
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connection successful.<br><br>";

    // Example query: List all tables in the database
    $stmt = $pdo->query("SHOW TABLES");

    echo "📋 Tables in database '<strong>$dbname</strong>':<br>";
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "- " . htmlspecialchars($row[0]) . "<br>";
    }

} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>

<?php

