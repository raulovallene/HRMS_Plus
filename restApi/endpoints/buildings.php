<?php
require_once __DIR__ . '/../config/api_header.php';
require_once __DIR__ . '/../config/db_kimco.php';
require_once __DIR__ . '/../config/auth.php';

// === AUTENTICACIÓN ===
requireAuth();

try {
    // Crear conexión usando la clase ya probada
    $db = new DatabaseKimco(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    // Ejecutar query simple
    $sql = "SELECT * FROM buildings LIMIT 5";
    $stmt = $pdo->query($sql);

    // Obtener resultados
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar JSON limpio
    echo json_encode([
        'ok' => true,
        'count' => count($rows),
        'data' => $rows
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
