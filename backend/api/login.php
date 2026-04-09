<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$email    = trim($data['email']    ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_active'] == 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Please verify your email before logging in.',
                'needs_verification' => true,
                'email' => $email
            ]);
            exit;
        }

        // Return user data (excluding password)
        unset($user['password']);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful!',
            'user' => $user
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
