<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

$db = new Database();
$pdo = $db->getConnection();

try {

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clients (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            birthdate DATE NOT NULL,
            cpf VARCHAR(14) NOT NULL UNIQUE,
            rg VARCHAR(20) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            street VARCHAR(100) NOT NULL,
            number VARCHAR(10) NOT NULL,
            neighborhood VARCHAR(100) NOT NULL,
            city VARCHAR(100) NOT NULL,
            state VARCHAR(2) NOT NULL,
            zip_code VARCHAR(10) NOT NULL,
            complement VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
        )
    ");

    echo "Tabelas criadas com sucesso.\n";

    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password) VALUES 
        (:name, :email, :password)
    ");
    $stmt->execute([
        'name' => 'Jonh Doe',
        'email' => 'john@example.com',
        'password' => password_hash('C_123456', PASSWORD_DEFAULT),
    ]);

    $pdo->exec("
        INSERT INTO clients (name, birthdate, cpf, rg, phone) VALUES
        ('Cliente Um', '1990-01-01', '12345678900', 'MG123456', '11999990001'),
        ('Cliente Dois', '1992-02-02', '98765432100', 'SP654321', '11999990002'),
        ('Cliente Três', '1994-03-03', '11122233344', 'RJ112233', '11999990003')
    ");

    $clientIds = $pdo->query("SELECT id FROM clients ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        INSERT INTO addresses (client_id, street, number, neighborhood, city, state, zip_code, complement)
        VALUES (:client_id, :street, :number, :neighborhood, :city, :state, :zip_code, :complement)
    ");

    $enderecos = [
        ['Rua A', '100', 'Centro', 'São Paulo', 'SP', '01001-000', 'Apto 101'],
        ['Rua B', '200', 'Bairro B', 'Rio de Janeiro', 'RJ', '20020-000', 'Casa'],
        ['Rua C', '300', 'Vila C', 'Belo Horizonte', 'MG', '30030-000', ''],
    ];

    foreach ($clientIds as $index => $clientId) {
        $dados = $enderecos[$index];
        $stmt->execute([
            'client_id' => $clientId,
            'street' => $dados[0],
            'number' => $dados[1],
            'neighborhood' => $dados[2],
            'city' => $dados[3],
            'state' => $dados[4],
            'zip_code' => $dados[5],
            'complement' => $dados[6],
        ]);
    }

    echo "Dados inseridos com sucesso.\n";
} catch (PDOException $e) {
    echo "Erro ao executar migração: " . $e->getMessage();
}
