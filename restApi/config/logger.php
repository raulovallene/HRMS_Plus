<?php
/**
 * logger.php
 * 
 * Registro centralizado de acciones para la API Kimco.
 * Guarda logs en la tabla sf_integration_log.
 */

require_once __DIR__ . '/db_kimco.php';

function logAction(string $endpoint, string $action, string $level = 'INFO', ?string $entityId = null)
{
    try {
        $db = new DatabaseKimco(__DIR__ . '/.env');
        $pdo = $db->connect();

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'BearerAPI';

        $stmt = $pdo->prepare("
            INSERT INTO sf_integration_log (logged_at, level, endpoint, action, entity_id, ip, user_agent)
            VALUES (NOW(), :level, :endpoint, :action, :entity_id, :ip, :user_agent)
        ");

        $stmt->execute([
            ':level' => strtoupper($level),
            ':endpoint' => $endpoint,
            ':action' => $action,
            ':entity_id' => $entityId,
            ':ip' => $ip,
            ':user_agent' => $userAgent
        ]);

    } catch (Throwable $e) {
        // Evitar loop infinito si falla el log
        error_log("Logger error: " . $e->getMessage());
    }
}
