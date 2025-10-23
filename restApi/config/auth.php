<?php
require_once __DIR__ . '/logger.php';

/**
 * auth.php
 * 
 * Middleware de autenticación Bearer Token para la API Kimco.
 * Lee el token desde /config/.env y valida la cabecera Authorization.
 */

require_once __DIR__ . '/db_kimco.php';

// 🔹 Cargar variables del .env si no existen aún
if (!getenv('API_KIMCO_BEARER_TOKEN')) {
    try {
        $dbLoader = new DatabaseKimco(__DIR__ . '/.env'); // esto carga el .env sin abrir conexión
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Failed to load .env file: ' . $e->getMessage()
            logAction('auth', 'Failed to load .env file', 'ERROR');
        ]);
        exit;
    }
}

function requireAuth()
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    // Si no hay header Authorization
    if (empty($authHeader)) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Unauthorized: missing Authorization header'
            logAction('auth', 'Missing Authorization header', 'ALERT');
        ]);
        exit;
    }

    // Formato inválido
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Unauthorized: invalid header format'
            logAction('auth', 'Invalid token provided', 'ERROR');
        ]);
        exit;
    }

    $token = trim($matches[1]);
    $expectedToken = getenv('API_KIMCO_BEARER_TOKEN');

    // Si no existe el token en el .env
    if (!$expectedToken) {
        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Server misconfiguration: missing API_KIMCO_BEARER_TOKEN'
            logAction('auth', 'Invalid token provided', 'ERROR');
        ]);
        exit;
    }

    // Si el token es incorrecto
    if (!hash_equals($expectedToken, $token)) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Unauthorized: invalid token'
            logAction('auth', 'Invalid token provided', 'ERROR');
        ]);
        exit;
    }

    // ✅ Si pasa todas las validaciones
    logAction('auth', 'Auth successful', 'INFO');
    return true;
}
