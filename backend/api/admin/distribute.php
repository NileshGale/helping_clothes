<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

// Support both JSON and Form Data
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$request_id  = $data['request_id']  ?? null;
$donation_id = $data['donation_id'] ?? null;
$admin_id    = $data['admin_id']    ?? null;
$proof_image = $data['proof_image'] ?? '';
$notes       = $data['notes']       ?? '';

if (!$request_id || !$notes) {
    echo json_encode(['success' => false, 'message' => 'Request mapping and notes are required.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Insert distribution
    $stmt = $pdo->prepare('INSERT INTO distributions (request_id, donation_id, admin_id, proof_image, notes, distributed_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$request_id, $donation_id, $admin_id, $proof_image, $notes]);

    // 2. Mark help request as fulfilled
    $stmt = $pdo->prepare('UPDATE help_requests SET status = "fulfilled" WHERE id = ?');
    $stmt->execute([$request_id]);

    // 3. Mark donation as delivered if it was linked
    if ($donation_id) {
        $stmt = $pdo->prepare('UPDATE donations SET status = "delivered" WHERE id = ?');
        $stmt->execute([$donation_id]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Distribution recorded successfully.']);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
