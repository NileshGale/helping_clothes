<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$user_id = $_GET['user_id'] ?? 1;

try {
    $stmt = $pdo->prepare('SELECT * FROM donations WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
    $stmt->execute([$user_id]);
    $donations = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'donations' => $donations
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
