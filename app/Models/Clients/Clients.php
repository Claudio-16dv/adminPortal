<?php

namespace App\Models\Clients;

use App\Core\Database;
use App\Traits\Sanitize;
use PDO;
use PDOException;
use InvalidArgumentException;

class Clients
{
    use Sanitize;

    private PDO $pdo;
    protected string $table = 'clients';

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function findById(int $id): ?array
    {
        $idKey = $this->validateIdentifier('id');

        $sql = "SELECT * FROM {$this->table} WHERE {$idKey} = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        return $client ?: null;
    }

    public function create(array $data): int
    {
        $columns = ['name', 'birthdate', 'cpf', 'rg', 'phone'];

        if (!$this->tableStructureIsValid($this->table, $columns)) {
            throw new \RuntimeException("Estrutura da tabela inválida.");
        }

        $this->ensureUniqueCpf($data['cpf']);

        $colKeys = array_map([$this, 'validateIdentifier'], $columns);
        $colList = implode(', ', $colKeys);
        $paramList = implode(', ', array_map(fn($c) => ":$c", $colKeys));

        $sql = "INSERT INTO {$this->table} ({$colList}) VALUES ({$paramList})";
        $stmt = $this->pdo->prepare($sql);

        foreach ($colKeys as $column) {
            $stmt->bindValue(":$column", $data[$column], PDO::PARAM_STR);
        }

        if (!$stmt->execute()) {
            throw new \RuntimeException("Erro ao inserir cliente.");
        }

        return (int) $this->pdo->lastInsertId();
    }

    private function ensureUniqueCpf(string $cpf): void
    {
        $cpfKey = $this->validateIdentifier('cpf');

        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$cpfKey} = :cpf";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cpf', $cpf, PDO::PARAM_STR);
        $stmt->execute();

        if ((int) $stmt->fetchColumn() > 0) {
            throw new \InvalidArgumentException("O CPF informado já está cadastrado.");
        }
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data)) {
            throw new \InvalidArgumentException("Nenhum campo informado para atualização.");
        }

        $allowedColumns = ['name', 'birthdate', 'cpf', 'rg', 'phone'];
        $columnsToUpdate = array_keys($data);

        foreach ($columnsToUpdate as $column) {
            if (!in_array($column, $allowedColumns, true)) {
                throw new \InvalidArgumentException("Campo não permitido: $column");
            }
        }

        if (!$this->tableStructureIsValid($this->table, $columnsToUpdate)) {
            throw new \RuntimeException("Estrutura da tabela inválida.");
        }

        $validatedCols = array_map([$this, 'validateIdentifier'], $columnsToUpdate);
        $setClause = implode(', ', array_map(fn($col) => "$col = :$col", $validatedCols));

        $idKey = $this->validateIdentifier('id');
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$idKey} = :id";

        $stmt = $this->pdo->prepare($sql);

        foreach ($validatedCols as $column) {
            $stmt->bindValue(":$column", $data[$column], PDO::PARAM_STR);
        }

        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $idKey = $this->validateIdentifier('id');

        $sql = "DELETE FROM {$this->table} WHERE {$idKey} = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
