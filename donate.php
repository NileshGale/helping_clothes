<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once 'db.php';

$user_id = $_POST['user_id'] ?? 1; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$category    = $_POST['category']    ?? '';
$user_name   = $_POST['name']        ?? '';
$mobile      = $_POST['mobile']      ?? '';
$address     = $_POST['address']     ?? '';
$description = $_POST['description'] ?? '';

if (!$category || !$user_name || !$mobile || !$address) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing.']);
    exit;
}


$image_path = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/donations/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('don_') . '.' . $file_ext;
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $image_path = $target_file;
    }
}

try {
    $stmt = $pdo->prepare('INSERT INTO donations (user_id, category, user_name, mobile, address, description, image_path, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, "pending", NOW())');
    $stmt->execute([$user_id, $category, $user_name, $mobile, $address, $description, $image_path]);

    echo json_encode([
        'success' => true,
        'message' => 'Donation submitted successfully!',
        'donation_id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
