<?php

namespace App\Traits;

use InvalidArgumentException;
use PDO;
use PDOException;

trait Sanitize
{
    private function validateIdentifier(string $name): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $name)) {
            throw new InvalidArgumentException("Identificador inválido: $name");
        }
        return $name;
    }

    private function tableStructureIsValid(string $table, array $columns): bool
    {
        try {
            $columnList = implode(", ", array_map([$this, 'validateIdentifier'], $columns));
            $tableSafe = $this->validateIdentifier($table);
            $sql = "SELECT $columnList FROM $tableSafe LIMIT 0";
            $this->pdo->prepare($sql)->execute();
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
}

