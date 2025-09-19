<?php

namespace App\Models\Clients;

use App\Core\Database;
use App\Traits\Sanitize;
use PDO;
use PDOException;

class Addresses
{
    use Sanitize;

    private PDO $pdo;
    protected string $table = 'addresses';

    public function __construct()
    {
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function getAddressByClientId(int $clientId): array
    {
        $clientKey = $this->validateIdentifier('client_id');

        $sql = "SELECT * FROM {$this->table} WHERE {$clientKey} = :client_id ORDER BY id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function addAddresses(int $clientId, array $addresses): void
    {
        $columns = ['client_id', 'street', 'number', 'neighborhood', 'city', 'state', 'zip_code', 'complement'];

        if (!$this->tableStructureIsValid($this->table, $columns)) {
            throw new \RuntimeException("Estrutura da tabela de endereços inválida.");
        }

        foreach ($addresses as $address) {
            $this->insertAddress($clientId, $address);
        }
    }

    public function updateAddresses(int $clientId, array $addresses): void
    {
        foreach ($addresses as $address) {
            if (isset($address['id'])) {
                $this->updateAddress($address['id'], $address);
            } else {
                $this->insertAddress($clientId, $address);
            }
        }
    }

    private function updateAddress(int $id, array $data): void
    {
        $columns = ['street', 'number', 'neighborhood', 'city', 'state', 'zip_code', 'complement'];

        if (!$this->tableStructureIsValid($this->table, $columns)) {
            throw new \RuntimeException("Estrutura da tabela inválida para atualização.");
        }

        $setClause = implode(', ', array_map(fn($col) => "$col = :$col", $columns));

        $sql = "UPDATE {$this->table} SET {$setClause} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);

        foreach ($columns as $col) {
            $stmt->bindValue(":$col", $data[$col] ?? '', PDO::PARAM_STR);
        }

        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function insertAddress(int $clientId, array $data): void
    {
        $sql = "INSERT INTO {$this->table}
                (client_id, street, number, neighborhood, city, state, zip_code, complement)
                VALUES
                (:client_id, :street, :number, :neighborhood, :city, :state, :zip_code, :complement)";
                
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindValue(':client_id', $clientId, PDO::PARAM_INT);
        $stmt->bindValue(':street', $data['street'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':number', $data['number'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':neighborhood', $data['neighborhood'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':city', $data['city'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':state', $data['state'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':zip_code', $data['zip_code'] ?? '', PDO::PARAM_STR);
        $stmt->bindValue(':complement', $data['complement'] ?? '', PDO::PARAM_STR);
        $stmt->execute();
    }
}
