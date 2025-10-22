<?php
/**
 * buildings.php (simplified)
 * 
 * Simple JSON endpoint for Kimco buildings data.
 * No auth, no headers, no logger — just raw DB read.
 */

require_once __DIR__ . '/../db_kimco.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // connect
    $db = new DatabaseKimco(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    // params
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 5000) : 100;

    // query
    $sql = "SELECT id, building_name, city, state, zip_code FROM buildings ORDER BY id ASC LIMIT :limit";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'ok' => true,
        'count' => count($rows),
        'data' => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
