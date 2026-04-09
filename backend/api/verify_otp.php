<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$otp   = $data['otp'] ?? '';

if (!$email || !$otp) {
    echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
    exit;
}

try {
    // Check if the OTP matches and is not expired
    $stmt = $pdo->prepare('
        SELECT ev.id, ev.user_id 
        FROM email_verifications ev
        JOIN users u ON u.id = ev.user_id
        WHERE u.email = ? AND ev.otp_code = ? AND ev.expires_at > NOW()
    ');
    $stmt->execute([$email, $otp]);
    $verification = $stmt->fetch();
    
    if ($verification) {
        // 1. Activate the user
        $stmt = $pdo->prepare('UPDATE users SET is_active = 1 WHERE id = ?');
        $stmt->execute([$verification['user_id']]);
        
        // 2. Remove the used OTP record
        $stmt = $pdo->prepare('DELETE FROM email_verifications WHERE id = ?');
        $stmt->execute([$verification['id']]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Email verified successfully! Your account is now active.'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'The code you entered is invalid or has expired.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
