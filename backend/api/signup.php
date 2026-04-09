<?php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/mail_config.php';

// Support both JSON and Form Data
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$name     = trim($data['name']     ?? '');
$email    = trim($data['email']    ?? '');
$mobile   = trim($data['mobile']   ?? '');
$password = $data['password']      ?? '';

if (!$name || !$email || !$mobile || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        exit;
    }

    // 1. Create user in 'inactive' state
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, mobile, password, is_active, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
    $stmt->execute([$name, $email, $mobile, $hashed]);
    $user_id = $pdo->lastInsertId();

    // 2. Generate initial OTP
    $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare('INSERT INTO email_verifications (user_id, otp_code, expires_at, created_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())');
    $stmt->execute([$user_id, $otp]);

    // 3. Send OTP via PHPMailer
    $mail_sent = false;
    $mail = getMailer();
    if ($mail) {
        try {
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your Email - Helping Hands';
            $mail->Body    = getOtpEmailBody($otp);
            $mail->send();
            $mail_sent = true;
        } catch (Exception $e) {
            // Mail failed, but user record exists. They can request resend later.
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Account created! A verification code has been sent to your email.',
        'email' => $email,
        'mail_sent' => $mail_sent
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
