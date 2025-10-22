<?php
/**
 * auth.php
 * 
 * Middleware de autenticación Bearer Token para la API Kimco.
 * Lee el token desde /config/.env y valida la cabecera Authorization.
 */

require_once __DIR__ . '/db_kimco.php';

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
        ]);
        exit;
    }

    // Formato inválido
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Unauthorized: invalid header format'
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
        ]);
        exit;
    }

    // Si el token es incorrecto
    if (!hash_equals($expectedToken, $token)) {
        http_response_code(401);
        echo json_encode([
            'ok' => false,
            'error' => 'Unauthorized: invalid token'
        ]);
        exit;
    }

    // ✅ Si pasa todas las validaciones
    return true;
}
