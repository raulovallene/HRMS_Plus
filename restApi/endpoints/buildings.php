<?php
require_once __DIR__ . '/../config/db_kimco.php';
require_once __DIR__ . '/../config/api_header.php';


try {
    // Crear conexión usando la clase ya probada
    $db = new DatabaseKimco(__DIR__ . '/../config/.env');
    $pdo = $db->connect();

    // Ejecutar query simple
    $sql = "SELECT * FROM buildings LIMIT 5";
    $stmt = $pdo->query($sql);

    // Obtener resultados
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mostrar en pantalla sin formatear (debug)
    echo "<pre>";
    print_r($rows);
    echo "</pre>";

} catch (Throwable $e) {
    echo "<pre>ERROR:\n" . $e->getMessage() . "</pre>";
}
