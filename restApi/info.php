<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

try {
    $db  = new Database();
    $pdo = $db->connect();

    // Alias seguro
    $stmt = $pdo->query("SELECT NOW() AS server_time");
    $row  = $stmt->fetch();

    echo json_encode([
        "status" => "ok",
        "message" => "Connection successful!",
        "mysql_time" => $row['server_time']
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => $e->getMessage()
    ]);
}
