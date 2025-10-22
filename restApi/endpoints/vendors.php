<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/logger.php';

header('Content-Type: application/json; charset=utf-8');
$endpoint = 'vendors';
requireAuth($endpoint);

try {
    $db = new Database(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    $sql = "SELECT 
                id, job_id, building_id, property_vendor_sfid,
                vendor_name, type, street, city, state, zip_code,
                phone_number, notes, last_updated
            FROM vendors
            ORDER BY id ASC";

    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logAction($endpoint, "Served " . count($rows) . " vendor records", 'INFO');

    echo json_encode([
        'ok' => true,
        'endpoint' => $endpoint,
        'count' => count($rows),
        'data' => $rows
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    logAction($endpoint, "Error: " . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal Server Error']);
}
?>