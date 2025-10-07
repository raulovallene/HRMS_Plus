<?php
require_once __DIR__ . '/db.php';

try {
    $db = new Database();

    echo "<pre>";
    echo "✅ DB_HOST: " . getenv('DB_HOST') . PHP_EOL;
    echo "✅ DB_USER: " . getenv('DB_USER') . PHP_EOL;
    echo "✅ DB_NAME: " . getenv('DB_NAME') . PHP_EOL;
    echo "✅ AZURE_CLIENT_ID: " . getenv('AZURE_CLIENT_ID') . PHP_EOL;
    echo "✅ AZURE_TENANT_ID: " . getenv('AZURE_TENANT_ID') . PHP_EOL;
    echo "✅ AZURE_REDIRECT_URI: " . getenv('AZURE_REDIRECT_URI') . PHP_EOL;
    echo "✅ AZURE_CLIENT_SECRET: " . substr(getenv('AZURE_CLIENT_SECRET'), 0, 6) . "..." . PHP_EOL;
    echo "</pre>";
} catch (Exception $e) {
    echo "<b>❌ Error:</b> " . $e->getMessage();
}
