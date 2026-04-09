<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT * FROM donations ORDER BY created_at DESC');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'update') {
        $id     = $_POST['id']     ?? '';
        $status = $_POST['status'] ?? '';
        
        if ($id && $status) {
            $stmt = $pdo->prepare('UPDATE donations SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Status updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing data.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
