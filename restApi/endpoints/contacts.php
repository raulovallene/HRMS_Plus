<?php
require_once __DIR__ . '/../config/api_header.php';
require_once __DIR__ . '/../config/db_kimco.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/logger.php';

// === AUTH ===
requireAuth();

try {
    $db = new DatabaseKimco(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    $sql = "SELECT * FROM contacts ORDER BY id ASC LIMIT 1000";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logAction('contacts', 'Returned ' . count($rows) . ' contacts', 'INFO');

    echo json_encode([
        'ok' => true,
        'count' => count($rows),
        'data' => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    logAction('contacts', 'Error: ' . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
