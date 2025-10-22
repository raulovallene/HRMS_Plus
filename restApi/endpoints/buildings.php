<?php
require_once __DIR__ . '/../db_kimco.php';
require_once __DIR__ . '/../config/api_header.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/logger.php';

header('Content-Type: application/json; charset=utf-8');

$endpoint = 'buildings';
requireAuth($endpoint); // 🔐 Autenticación obligatoria

try {
    $db = new DatabaseKimco(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    $stmt = $pdo->query("SELECT * FROM buildings LIMIT 10");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    logAction($endpoint, 'Successfully retrieved building data', 'INFO');

    echo json_encode(['ok' => true, 'count' => count($data), 'data' => $data]);
} catch (Throwable $e) {
    logAction($endpoint, 'Error: ' . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal Server Error']);
}
