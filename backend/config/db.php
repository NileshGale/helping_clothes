<?php
$host = 'localhost';
$dbname = 'helping_hands';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // If called via API, return JSON
    if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB connection failed: ' . $e->getMessage()]);
        exit;
    }
    // Otherwise just fail
    die("DB connection failed: " . $e->getMessage());
}
?>
