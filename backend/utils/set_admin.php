<?php
require_once __DIR__ . '/../config/db.php';

// Credentials from USER
$admin_email = 'Khiratkarriya@gmail.com';
$admin_pass  = '12345678';
$admin_name  = 'Khiratkar Riya';

try {
    // 0. Ensure Table Columns exist (Self-Healing)
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('role', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER password");
    }
    if (!in_array('is_active', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
    }

    // 1. Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    $user = $stmt->fetch();

    $hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

    if ($user) {
        // Update existing
        $stmt = $pdo->prepare("UPDATE users SET password = ?, role = 'admin', is_active = 1 WHERE id = ?");
        $stmt->execute([$hashed_pass, $user['id']]);
        $message = "Admin password updated for existing user: $admin_email";
    } else {
        // Create new
        $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role, is_active, created_at) VALUES (?, ?, ?, ?, 'admin', 1, NOW())");
        $stmt->execute([$admin_name, $admin_email, '9876543210', $hashed_pass]);
        $message = "New admin account created: $admin_email";
    }

    echo "<h1>Success!</h1><p>$message</p><p>Password set to: <strong>$admin_pass</strong></p><p><a href='../../frontend/pages/login.html'>Go to Unified Login</a></p>";

} catch (PDOException $e) {
    echo "<h1>Error</h1><p>" . $e->getMessage() . "</p>";
}
?>
