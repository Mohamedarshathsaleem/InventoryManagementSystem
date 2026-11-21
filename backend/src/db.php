<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Lazily creates one shared PDO connection per request.
 * Connection settings come from environment variables so no credentials live in the repo.
 */
class db
{
    private ?PDO $pdo = null;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function getPDO(): PDO
    {
        if ($this->pdo === null) {
            $host    = getenv('DB_HOST') ?: '127.0.0.1';
            $port    = getenv('DB_PORT') ?: '3306';
            $name    = getenv('DB_DATABASE') ?: 'inventory_management_system';
            $user    = getenv('DB_USERNAME') ?: 'root';
            $pass    = getenv('DB_PASSWORD') ?: '';
            $dsn     = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return $this->pdo;
    }
}
