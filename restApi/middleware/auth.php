<?php
/**
 * auth.php
 *
 * Middleware de autenticación Bearer Token
 * para proteger los endpoints de la API Kimco ↔ Salesforce.
 */

require_once __DIR__ . '/../db_kimco.php';
require_once __DIR__ . '/../utils/logger.php';

/**
 * Verifica autenticación por Bearer Token.
 * 
 * @param string $endpoint - nombre del endpoint actual (para logging)
 */
function requireAuth(string $endpoint)
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        logAction($endpoint, 'Unauthorized request (no token provided)', 'WARN');
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized: missing token']);
        exit;
    }

    $token = trim($matches[1]);
    $expectedToken = getenv('API_KIMCO_BEARER_TOKEN');

    if (!$expectedToken) {
        logAction($endpoint, 'Environment variable API_KIMCO_BEARER_TOKEN not set', 'ERROR');
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Server misconfiguration']);
        exit;
    }

    if (!hash_equals($expectedToken, $token)) {
        logAction($endpoint, 'Unauthorized request (invalid token)', 'WARN');
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'Unauthorized: invalid token']);
        exit;
    }

    // ✅ Authorized
    logAction($endpoint, 'Authorized access granted', 'INFO');
}
