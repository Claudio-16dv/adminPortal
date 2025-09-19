<?php

namespace App\Models\Users;

use App\Core\Database;
use App\Traits\Sanitize;
use PDO;

class Users
{
    use Sanitize;

    private PDO $pdo;

    protected string $table = 'users';
    
    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function createUser(array $data): int
    {
        if (!$this->tableStructureIsValid($this->table, ['name', 'email', 'password'])) {
            throw new \RuntimeException("Estrutura da tabela inválida.");
        }
    
        $nameKey = $this->validateIdentifier('name');
        $emailKey = $this->validateIdentifier('email');
        $passwordKey = $this->validateIdentifier('password');
    
        $sql = "INSERT INTO users ({$nameKey}, {$emailKey}, {$passwordKey}) 
                VALUES (:{$nameKey}, :{$emailKey}, :{$passwordKey})";
        
        $stmt = $this->pdo->prepare($sql);
    
        $stmt->bindValue(":{$nameKey}", $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(":{$emailKey}", $data['email'], PDO::PARAM_STR);
        $stmt->bindValue(":{$passwordKey}", $data['password'], PDO::PARAM_STR);
    
        if (!$stmt->execute()) {
            throw new \RuntimeException("Erro ao inserir o usuário.");
        }
    
        return (int) $this->pdo->lastInsertId();
    }

    public function emailExists(string $email): bool
    {
        $this->validateIdentifier('email');

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        return (int) $stmt->fetchColumn() > 0;
    }

    public function loginUser(string $email): ?array
    {
        $this->validateIdentifier('email');

        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }
}
