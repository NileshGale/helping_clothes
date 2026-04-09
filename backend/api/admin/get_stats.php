<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

try {
    $donations = $pdo->query('SELECT COUNT(*) FROM donations')->fetchColumn();
    $requests  = $pdo->query('SELECT COUNT(*) FROM help_requests WHERE status != "fulfilled"')->fetchColumn();
    $users     = $pdo->query('SELECT COUNT(*) FROM users WHERE role = "user"')->fetchColumn();
    $impact    = $pdo->query('SELECT COUNT(*) FROM distributions')->fetchColumn();

    echo json_encode([
        'success' => true,
        'stats' => [
            'donations' => $donations,
            'requests'  => $requests,
            'users'     => $users,
            'impact'    => $impact
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
