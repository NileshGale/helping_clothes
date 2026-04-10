<?php
header('Content-Type: application/json');
require_once '../../config/db.php';

try {
    // 1. Donations by Category
    $stmt = $pdo->query('SELECT category, COUNT(*) as count FROM donations GROUP BY category');
    $categories = $stmt->fetchAll();

    // 2. Monthly Donations Trend (Last 6 Months)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') as label, COUNT(*) as count 
        FROM donations 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY label 
        ORDER BY MIN(created_at) ASC
    ");
    $donationsTrend = $stmt->fetchAll();

    // 3. User Growth (Last 6 Months)
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at, '%b %Y') as label, COUNT(*) as count 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) AND role = 'user'
        GROUP BY label 
        ORDER BY MIN(created_at) ASC
    ");
    $usersTrend = $stmt->fetchAll();

    // 4. Request Fulfillment
    $stmt = $pdo->query('SELECT status, COUNT(*) as count FROM help_requests GROUP BY status');
    $requestStats = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'analytics' => [
            'categories'     => $categories,
            'donationsTrend' => $donationsTrend,
            'usersTrend'     => $usersTrend,
            'requestStats'   => $requestStats
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
