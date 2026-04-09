<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$user_id     = trim($data['user_id']     ?? '');
$email       = trim($data['email']       ?? '');
$name        = trim($data['name']        ?? '');
$mobile      = trim($data['mobile']      ?? '');
$city        = trim($data['city']        ?? '');
$bio         = trim($data['bio']         ?? '');
$old_pass    = $data['old_password']     ?? '';
$new_pass    = $data['new_password']     ?? '';

if (!$user_id || !$email) {
    echo json_encode(['success' => false, 'message' => 'User ID and Email are required.']);
    exit;
}

try {
    // 1. Get current user data
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // 2. Check if email is being changed and if it's available
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'This email is already registered with another account.']);
            exit;
        }
    }

    // 3. Handle Password Change if requested
    $password_sql = "";
    $params = [$name, $email, $mobile, $city, $bio];

    if ($new_pass) {
        if (!$old_pass) {
            echo json_encode(['success' => false, 'message' => 'Current password is required to set a new one.']);
            exit;
        }

        // Verify old password
        if (!password_verify($old_pass, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
            exit;
        }

        // Hash new password
        $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
        $password_sql = ", password = ?";
        $params[] = $hashed;
    }

    // 3. Update Profile
    $sql = "UPDATE users SET name = ?, email = ?, mobile = ?, city = ?, bio = ? $password_sql WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Fetch updated user data (excluding password)
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $updatedUser = $stmt->fetch();
    unset($updatedUser['password']);

    echo json_encode([
        'success' => true,
        'message' => 'Account updated successfully!',
        'user' => $updatedUser
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
