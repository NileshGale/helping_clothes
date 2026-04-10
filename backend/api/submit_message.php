<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);

$name    = $data['name']    ?? '';
$email   = $data['email']   ?? '';
$phone   = $data['phone']   ?? '';
$subject = $data['subject'] ?? 'General Inquiry';
$message = $data['message'] ?? '';

if (!$name || !$email || !$message) {
    echo json_encode(['success' => false, 'message' => 'Name, Email, and Message are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
    $stmt->execute([$name, $email, $phone, $subject, $message]);
    
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
