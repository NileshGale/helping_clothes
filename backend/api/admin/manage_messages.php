<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'mark_read') {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare('UPDATE contact_messages SET is_read = 1 WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Message marked as read.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID.']);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
