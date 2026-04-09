<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT id, name, email, city, role FROM users ORDER BY created_at DESC');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'delete') {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'User deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
