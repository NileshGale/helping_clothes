<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('
            SELECT hr.*, u.name as user_name, u.email as user_email 
            FROM help_requests hr 
            JOIN users u ON hr.user_id = u.id 
            ORDER BY hr.created_at DESC
        ');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id     = $data['id']     ?? '';
        $status = $data['status'] ?? '';

        if ($id && $status) {
            $stmt = $pdo->prepare('UPDATE help_requests SET status = ? WHERE id = ?');
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true, 'message' => 'Request updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing data.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
