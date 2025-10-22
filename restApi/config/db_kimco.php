<?php
/**
 * db_kimco.php
 * 
 * Database handler for Kimco Salesforce Integration API.
 * Loads environment variables from /config/.env
 * and provides a PDO connection using secure settings.
 */

class DatabaseKimco {
    private $pdo;

    /**
     * Constructor
     * Loads environment variables from /config/.env
     */
    public function __construct($configPath = __DIR__ . '/../config/.env') {
        if (!file_exists($configPath)) {
            throw new Exception("Environment file not found at: " . $configPath);
        }

        // Load .env file manually
        $lines = file($configPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Make env vars available globally
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Create (or reuse) a PDO connection.
     * Returns PDO instance.
     */
    public function connect() {
        if ($this->pdo) {
            return $this->pdo;
        }

        $host = getenv('DB_HOST');
        $port = getenv('DB_PORT') ?: 3306;
        $name = getenv('DB_KIMCO_NAME'); // 👈 changed here
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASSWORD');
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        if (!$name) {
            throw new Exception("Environment variable DB_KIMCO_NAME not set in .env file");
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $name, $charset);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'",
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }

        return $this->pdo;
    }
}
