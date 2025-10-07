<?php
/**
 * 🌍 Environment Loader
 * Loads all variables from .env into getenv() / putenv()
 * so they can be used across your scripts securely.
 */

if (!function_exists('loadEnv')) {
    function loadEnv($path)
    {
        if (!file_exists($path)) {
            throw new Exception("Missing .env file at: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Only set if not already set
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

try {
    loadEnv(__DIR__ . '/.env');

    // ✅ Optional: log loaded variables for debugging
    $debugFile = __DIR__ . '/../debug_env.log';
    $vars = [
        'DB_HOST' => getenv('DB_HOST'),
        'DB_USER' => getenv('DB_USER'),
        'DB_NAME' => getenv('DB_NAME'),
        'AZURE_CLIENT_ID' => getenv('AZURE_CLIENT_ID'),
        'AZURE_TENANT_ID' => getenv('AZURE_TENANT_ID'),
        'AZURE_CLIENT_SECRET' => substr(getenv('AZURE_CLIENT_SECRET'), 0, 6) . '...',
        'AZURE_REDIRECT_URI' => getenv('AZURE_REDIRECT_URI'),
    ];
    file_put_contents($debugFile, "[ENV LOADED " . date('Y-m-d H:i:s') . "]\n" . print_r($vars, true) . "\n\n", FILE_APPEND);

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/../debug_env.log', "[ENV ERROR] " . $e->getMessage() . "\n", FILE_APPEND);
}
