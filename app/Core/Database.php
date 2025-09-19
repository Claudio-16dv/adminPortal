<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    public $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';

        $driver = $databaseConfig['DB_CONNECTION'] ?? 'mysql';
        $host   = $databaseConfig['DB_HOST'] ?? 'localhost';
        $port   = $databaseConfig['DB_PORT'] ?? ($driver === 'mysql' ? '3306' : '5432');
        $db     = $databaseConfig['DB_NAME'] ?? '';
        $user   = $databaseConfig['DB_USER'] ?? '';
        $pass   = $databaseConfig['DB_PASS'] ?? '';

        $dsn = "$driver:host=$host";
        if (!empty($port)) $dsn .= ";port=$port";
        if (!empty($db))   $dsn .= ";dbname=$db";
        if ($driver === 'mysql') $dsn .= ";charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao conectar no banco: ' . $e->getMessage()]);
            exit;
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
