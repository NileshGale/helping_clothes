<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

$action = $_GET['action'] ?? '';

try {
    if ($action === 'list') {
        $stmt = $pdo->query('SELECT id, name, email, mobile, city, role, bio, created_at FROM users ORDER BY created_at DESC');
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data]);
    } 
    elseif ($action === 'update') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id     = $data['id']     ?? '';
        $name   = $data['name']   ?? '';
        $mobile = $data['mobile'] ?? '';
        $city   = $data['city']   ?? '';
        $role   = $data['role']   ?? 'user';
        $bio    = $data['bio']    ?? '';

        if ($id && $name) {
            $stmt = $pdo->prepare('UPDATE users SET name = ?, mobile = ?, city = ?, role = ?, bio = ? WHERE id = ?');
            $stmt->execute([$name, $mobile, $city, $role, $bio, $id]);
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing required user information.']);
        }
    }
    elseif ($action === 'delete') {
        $id = $_GET['id'] ?? '';
        if ($id) {
            // Safety: Don't delete the last admin or yourself easily logic can be added here
            $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'User deleted permanently.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found or cannot delete an admin.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing ID.']);
        }
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
