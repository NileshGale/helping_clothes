<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);


$name   = trim($data['name']   ?? $_POST['name']   ?? '');
$email  = trim($data['email']  ?? $_POST['email']  ?? '');
$mobile = trim($data['mobile'] ?? $_POST['mobile'] ?? '');
$password = $data['password']  ?? $_POST['password'] ?? '';


if (!$name || !$email || !$mobile || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}

if (!preg_match('/^[0-9]{10}$/', $mobile)) {
    echo json_encode(['success' => false, 'message' => 'Mobile number must be 10 digits.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Email already registered.']);
    exit;
}


$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare('INSERT INTO users (name, email, mobile, password, created_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->execute([$name, $email, $mobile, $hashed]);

echo json_encode([
    'success' => true,
    'message' => 'Account created successfully!',
    'user_id' => $pdo->lastInsertId()
]);
?>
