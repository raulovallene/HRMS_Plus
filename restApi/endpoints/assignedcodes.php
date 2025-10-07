<?php
require_once __DIR__ . '/../config/api_header.php';
require_once __DIR__ . '/../config/db.php';

// Mostrar errores (solo durante desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new Database();
    $pdo = $db->connect();

    // Leer JSON del cuerpo de la petición
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        throw new Exception("No input data received or invalid JSON");
    }

    // Validar campos requeridos
    $required = ['codeId', 'idUser', 'caseClient', 'description', 'date'];
    foreach ($required as $field) {
        if (!isset($input[$field]) || $input[$field] === '') {
            throw new Exception("Missing required field: $field");
        }
    }

    // Preparar el INSERT con los nombres REALES de las columnas
    $stmt = $pdo->prepare("
        INSERT INTO assignedcodes (idCode, idUser, `case`, description, `date`)
        VALUES (:idCode, :idUser, :case, :description, :date)
    ");

    $stmt->execute([
        ':idCode' => $input['codeId'],
        ':idUser' => $input['idUser'],
        ':case' => $input['caseClient'],
        ':description' => $input['description'],
        ':date' => $input['date']
    ]);

    echo json_encode([
        'status' => 'ok',
        'message' => 'Code assigned successfully',
        'inserted_id' => $pdo->lastInsertId()
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
