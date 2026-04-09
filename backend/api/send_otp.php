<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/mail_config.php';

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (!$email) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}

// Generate a 6-digit OTP
$otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

try {
    // Find user
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }
    
    $user_id = $user['id'];
    
    // Delete old OTPs for this user to avoid confusion
    $stmt = $pdo->prepare('DELETE FROM email_verifications WHERE user_id = ?');
    $stmt->execute([$user_id]);
    
    // Insert new OTP with 10-minute expiry
    $stmt = $pdo->prepare('INSERT INTO email_verifications (user_id, otp_code, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())');
    $stmt->execute([$user_id, $otp]);
    
    // Send Formatted HTML Email
    $mail = getMailer();
    if ($mail) {
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - Helping Hands';
        
        // Styled HTML Body from template
        $mail->Body = getOtpEmailBody($otp);
        
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to initialize email service.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}
?>
