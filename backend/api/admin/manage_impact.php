<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('
            SELECT d.*, hr.category as original_category, u.name as admin_name 
            FROM distributions d
            LEFT JOIN help_requests hr ON d.request_id = hr.id
            LEFT JOIN users u ON d.admin_id = u.id
            ORDER BY d.distributed_at DESC
        ');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'create') {
        $data = json_decode(file_get_contents('php://input'), true);
        $req_id     = $data['request_id']   ?? null;
        $admin_id   = $data['admin_id']     ?? null;
        $product    = $data['product_name'] ?? '';
        $receiver   = $data['receiver_name'] ?? '';
        $image      = $data['proof_image']  ?? '';
        $date       = $data['date']         ?? date('Y-m-d');
        $notes      = $data['notes']        ?? '';

        if (!$product || !$receiver || !$date) {
            echo json_encode(['success' => false, 'message' => 'Product name, receiver name and date are required.']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO distributions (request_id, product_name, receiver_name, admin_id, proof_image, notes, distributed_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$req_id, $product, $receiver, $admin_id, $image, $notes, $date]);

        // If it was linked to a request, mark it fulfilled
        if ($req_id) {
            $stmt = $pdo->prepare('UPDATE help_requests SET status = "fulfilled" WHERE id = ?');
            $stmt->execute([$req_id]);
        }

        echo json_encode(['success' => true, 'message' => 'Impact proof recorded successfully!']);
    }
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id         = $data['id']           ?? null;
        $product    = $data['product_name'] ?? '';
        $receiver   = $data['receiver_name'] ?? '';
        $image      = $data['proof_image']  ?? '';
        $date       = $data['date']         ?? '';
        $notes      = $data['notes']        ?? '';

        if (!$id || !$product || !$receiver) {
            echo json_encode(['success' => false, 'message' => 'ID, Product and Receiver are required.']);
            exit;
        }

        $stmt = $pdo->prepare('UPDATE distributions SET product_name = ?, receiver_name = ?, proof_image = ?, notes = ?, distributed_at = ? WHERE id = ?');
        $stmt->execute([$product, $receiver, $image, $notes, $date, $id]);
        echo json_encode(['success' => true, 'message' => 'Impact proof updated.']);
    }
    elseif ($action === 'delete') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM distributions WHERE id = ?');
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Impact proof removed.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
