<?php
require_once __DIR__ . '/../config/api_header.php';
require_once __DIR__ . '/../config/db.php';

try {
    $db = new Database();
    $pdo = $db->connect();

    $brandParam = $_GET['brandId'] ?? null;
    $roleId     = $_GET['roleId'] ?? null;

    if ($brandParam === null || $roleId === null) {
        throw new Exception("Missing parameters");
    }

    $brandIds = array_filter(array_map('intval', explode(',', $brandParam)));

    // 🧠 Query base con JOIN a brands
    if ($roleId == 1) {
        // Admin: todos los códigos con nombre de marca
        $query = "
           SELECT c.idcodes, c.idBrand, c.description, c.code, c.createdAt, c.validUntil, brands.name AS `brandName`, c.status
FROM codes c
INNER JOIN (
    SELECT description, MIN(idcodes) AS firstId
    FROM codes
    WHERE status = 1
    GROUP BY description
) AS firstPerGroup
ON c.idcodes = firstPerGroup.firstId
INNER JOIN brands ON brands.idbrands = c.idBrand;
        ";
        $stmt = $pdo->prepare($query);
    } else {
        if (empty($brandIds)) {
            throw new Exception("Invalid or empty brand list");
        }

        $placeholders = implode(',', array_map(fn($i) => ":b$i", array_keys($brandIds)));
        $query = "
SELECT c.idcodes, c.idBrand, c.description, c.code, c.createdAt, c.validUntil, brands.name AS `brandName`, c.status
FROM codes c
INNER JOIN (
    SELECT description, MIN(idcodes) AS firstId
    FROM codes
    WHERE status = 1
    GROUP BY description
) AS firstPerGroup
ON c.idcodes = firstPerGroup.firstId
INNER JOIN brands ON brands.idbrands = c.idBrand
WHERE c.idBrand IN ($placeholders)
        ";
        $stmt = $pdo->prepare($query);

        foreach ($brandIds as $i => $id) {
            $stmt->bindValue(":b$i", $id, PDO::PARAM_INT);
        }
    }

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'ok',
        'count'  => count($data),
        'data'   => $data
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
