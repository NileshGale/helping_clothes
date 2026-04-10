<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data     = json_decode(file_get_contents('php://input'), true);
$user_id  = $data['user_id']  ?? null;
$name     = $data['name']     ?? '';
$mobile   = $data['mobile']   ?? '';
$alt_pass = $data['alternate_mobile'] ?? '';
$address  = $data['address']  ?? '';
$category = $data['category'] ?? '';
$items    = $data['items']    ?? '';
$reason   = $data['reason']   ?? '';

if (!$user_id || !$name || !$mobile || !$address || !$category || !$items || !$reason) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO help_requests (user_id, contact_name, mobile, alternate_mobile, address, category, items, reason, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending", NOW())');
    $stmt->execute([$user_id, $name, $mobile, $alt_pass, $address, $category, $items, $reason]);

    echo json_encode(['success' => true, 'message' => 'Your request has been submitted.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
