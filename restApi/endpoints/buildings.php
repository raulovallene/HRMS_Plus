<?php
/**
 * buildings.php
 * 
 * Endpoint REST para exponer los datos de edificios (buildings)
 * desde la base de datos kimco_sf hacia Salesforce u otros sistemas.
 * 
 * Incluye:
 *  - Autenticación vía Bearer Token (auth.php)
 *  - Logging en sf_integration_log
 *  - Parámetros opcionales: limit, updated_since
 */

require_once __DIR__ . '/../db_kimco.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/logger.php';
require_once __DIR__ . '/../config/api_header.php';


// === CONFIGURACIÓN DE RESPUESTA ===
header('Content-Type: application/json; charset=utf-8');
$endpoint = 'buildings';

// === AUTENTICACIÓN ===
requireAuth($endpoint);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // === CONEXIÓN A BASE DE DATOS ===
    $db = new DatabaseKimco(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    // === PARÁMETROS OPCIONALES ===
    $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 5000) : 1000;
    $updatedSince = $_GET['updated_since'] ?? null;

    // === QUERY BASE ===
    $sql = "
        SELECT 
            id,
            job_id,
            building_sfid,
            building_mri_id,
            building_name,
            street,
            city,
            state,
            zip_code,
            region,
            sub_region,
            last_updated
        FROM buildings
    ";

    // === CONDICIÓN OPCIONAL POR FECHA ===
    $params = [];
    if (!empty($updatedSince)) {
        $sql .= " WHERE last_updated >= :updated_since";
        $params[':updated_since'] = $updatedSince;
    }

    $sql .= " ORDER BY id ASC LIMIT :limit";
    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);

    // === EJECUTAR CONSULTA ===
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // === LOG DE ÉXITO ===
    logAction($endpoint, "Served " . count($rows) . " building records", 'INFO');

    // === RESPUESTA JSON ===
    echo json_encode([
        'ok' => true,
        'endpoint' => $endpoint,
        'count' => count($rows),
        'data' => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // === LOG DE ERROR ===
    logAction($endpoint, "Error: " . $e->getMessage(), 'ERROR');

    http_response_code(500);
