<?php
// /api/utils/logger.php
require_once __DIR__ . '/../db.php';

function logAction(string $endpoint, string $message, string $level = 'INFO', ?string $entityId = null): void {
    try {
        $db = new Database(__DIR__ . '/../config/.env');
        $pdo = $db->connect();

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'n/a';
        $line = sprintf("[%s] - %s", gmdate('Y-m-d H:i:s'), $message);

        $stmt = $pdo->prepare("
            INSERT INTO sf_integration_log (logged_at, level, endpoint, action, entity_id, ip, user_agent)
            VALUES (UTC_TIMESTAMP(), :level, :endpoint, :action, :entity_id, :ip, :ua)
        ");
        $stmt->execute([
            ':level' => $level,
            ':endpoint' => $endpoint,
            ':action' => $line,
            ':entity_id' => $entityId,
            ':ip' => $ip,
            ':ua' => $ua
        ]);
    } catch (Throwable $e) {
        error_log('Logger error: ' . $e->getMessage());
    }
}
?>