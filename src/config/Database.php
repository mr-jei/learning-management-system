<?php

class Database {
    private string $host;
    private string $port;
    private string $db_name;
    private string $username;
    private string $password;
    public ?PDO $conn = null;

    public function __construct() {
        // Override with environment variables if set, otherwise use local defaults.
        // XAMPP's default MySQL root password is an empty string "".
        $this->host     = getenv('LMS_DB_HOST') ?: '127.0.0.1';
        $this->port     = getenv('LMS_DB_PORT') ?: '3306';
        $this->db_name  = getenv('LMS_DB_NAME') ?: 'lms_db';
        $this->username = getenv('LMS_DB_USER') ?: 'root';
        $this->password = getenv('LMS_DB_PASS') !== false ? getenv('LMS_DB_PASS') : 'Samedara_1010';
    }

    /**
     * @throws RuntimeException when the connection fails
     */
    public function getConnection(): PDO {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage(), 0, $e);
            }
        }
        return $this->conn;
    }
}
