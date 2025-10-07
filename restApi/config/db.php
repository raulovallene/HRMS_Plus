<?php
class Database {
    private $pdo;

    // Load .env from /config folder
    public function __construct($configPath = __DIR__ . '/.env') {
        if (!file_exists($configPath)) {
            throw new Exception("Environment file not found at: " . $configPath);
        }

        // Load environment variables
        $lines = file($configPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!str_contains($line, '=')) continue;

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Make available across PHP environments
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    // Database connection
    public function connect() {
        if ($this->pdo) return $this->pdo;

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            getenv('DB_HOST'),
            getenv('DB_NAME'),
            getenv('DB_CHARSET') ?: 'utf8mb4'
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO(
                $dsn,
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                $options
            );
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }

        return $this->pdo;
    }
}
