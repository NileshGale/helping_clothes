<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT * FROM feedback ORDER BY created_at DESC');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'approve') {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare('UPDATE feedback SET is_published = 1 WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Feedback approved and published.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID.']);
        }
    }
    elseif ($action === 'delete') {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM feedback WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Feedback removed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID.']);
        }
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
