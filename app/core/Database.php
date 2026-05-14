<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?self $instance = null;

    private PDO $pdo;

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function connect(): void
    {
        try {
            $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
            $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: '';
            $username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: '';
            $password = $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '';

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $dbname);

            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('Database connection error: ' . $exception->getMessage());
            throw $exception;
        }
    }

    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($params);

            return $statement;
        } catch (PDOException $exception) {
            error_log('Database query error: ' . $exception->getMessage());
            throw $exception;
        }
    }

    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
}
