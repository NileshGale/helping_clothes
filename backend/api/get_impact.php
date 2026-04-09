<?php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // We join with help_requests to get the category and story
    $stmt = $pdo->query('
        SELECT 
            d.proof_image, d.notes as story, d.distributed_at,
            hr.category,
            u.name as receiver_name
        FROM distributions d
        JOIN help_requests hr ON d.request_id = hr.id
        JOIN users u ON hr.user_id = u.id
        ORDER BY d.distributed_at DESC
    ');
    $impact = $stmt->fetchAll();

    echo json_encode(['success' => true, 'impact' => $impact]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
