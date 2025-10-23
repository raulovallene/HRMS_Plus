<?php

/**
 * db_kimco.php
 * 
 * Simple DB connection handler for Kimco Salesforce Integration (no auth, no logger).
 */

class DatabaseKimco {
    private $pdo;

    public function __construct($configPath = __DIR__ . '/../config/.env') {
        if (!file_exists($configPath)) {
            throw new Exception("Environment file not found at: " . $configPath);
        }

        // Load .env variables
        $lines = file($configPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    public function connect() {
        if ($this->pdo) return $this->pdo;

        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: 3306;
        $name = getenv('DB_KIMCO_NAME');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        if (!$name) {
            throw new Exception("Missing DB_KIMCO_NAME in .env file");
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $user, $pass, $options);
        return $this->pdo;
    }
}

// === Direct call test ===
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $db = new DatabaseKimco(__DIR__ . '/../config/.env');
        $pdo = $db->connect();
        echo json_encode(['ok' => true, 'message' => '✅ Connection success to kimco_sf database']);
    } catch (Throwable $e) {
        require_once __DIR__ . '/logger.php';
        logAction('db_kimco', 'Database connection failed: ' . $e->getMessage(), 'ERROR');
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
}
