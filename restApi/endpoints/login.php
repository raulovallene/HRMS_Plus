<?php
require_once __DIR__ . '/../config/api_header.php';
require_once __DIR__ . '/../config/env_loader.php';
require_once __DIR__ . '/../config/db.php';

try {
    $db = new Database();
    $pdo = $db->connect();

    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    if (!$username) {
        throw new Exception("Username required");
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found");
    }

    // ✅ SSO user flow
    if ($user['sso'] == 1) {
        $tenantId = getenv('AZURE_TENANT_ID');
        $clientId = getenv('AZURE_CLIENT_ID');
        $redirectUri = urlencode(getenv('AZURE_REDIRECT_URI'));
        $scope = urlencode(getenv('AZURE_SCOPES') ?: 'openid profile email offline_access');

        $authUrl = "https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/authorize?"
            . "client_id={$clientId}&response_type=code&redirect_uri={$redirectUri}&scope={$scope}&state={$username}";

        echo json_encode(['status' => 'sso', 'redirect' => $authUrl]);
        exit;
    }

    //  Local user authentication
    if (!$password) {
        echo json_encode(['status' => 'password_required']);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        throw new Exception("Invalid password");
    }

    //  Correct table name (usersBrand)
    $brandStmt = $pdo->prepare("
        SELECT b.idbrands AS id, b.name AS name
        FROM `usersBrand` ub
        INNER JOIN `brands` b ON ub.idBrand = b.idbrands
        WHERE ub.idUser = :idUser
    ");
    $brandStmt->execute([':idUser' => $user['idusers']]);
    $brands = $brandStmt->fetchAll(PDO::FETCH_ASSOC);

    unset($user['password']);
    $user['brands'] = $brands;

    echo json_encode(['status' => 'ok', 'user' => $user]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
