<?php

use PHPUnit\Framework\TestCase;
use App\Services\Clients\ClientService;
use InvalidArgumentException;

class ClientsTest extends TestCase
{
    private ClientService $clientService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clientService = new ClientService();
    }

    protected function tearDown(): void
    {
        $clients = $this->clientService->getAllClients();
        foreach ($clients as $client) {
            if (isset($client['id'])) {
                $this->clientService->deleteClient($client['id']);
            }
        }
        parent::tearDown();
    }

    public function testGetClientForEditReturnsCorrectClient(): void
    {
        $payload = [
            'name' => 'Cliente Original',
            'birthdate' => '1992-04-04',
            'cpf' => '12345678901',
            'rg' => uniqid('RG'),
            'phone' => '11988887777',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        $response = $this->clientService->createClient($payload);
        $createdId = $response['id']; 

        $result = $this->clientService->getClientForEdit($createdId); 

        $this->assertIsArray($result);
        $this->assertEquals($createdId, $result['id']);

        $this->clientService->deleteClient($createdId);
    }

    public function testCreateClientWithInvalidCpf(): void
    {

        $clientDuplicateCpf = [
            'name' => 'Cliente Com CPF Duplicado',
            'birthdate' => '1990-01-01',
            'cpf' => '12345678901',
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        $this->clientService->createClient($clientDuplicateCpf);

        try {
            $this->clientService->createClient($clientDuplicateCpf);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("O CPF informado já está cadastrado.", $e->getMessage());
        }

        $clients = $this->clientService->getAllClients();
        foreach ($clients as $client) {
            if ($client['cpf'] === '12345678901') {
                $this->clientService->deleteClient($client['id']);
            }
        }
    }

    public function testUpdateClientUpdatesData(): void
    {
        $payload = [
            'name' => 'Cliente Original',
            'birthdate' => '1992-04-04',
            'cpf' => '12345678901',
            'rg' => uniqid('RG'),
            'phone' => '11988887777',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        $response = $this->clientService->createClient($payload);
        $createdId = $response['id']; 

        $updateData = [
            'name' => 'Cliente Atualizado',
            'birthdate' => $payload['birthdate'],
            'cpf' => $payload['cpf'],
            'rg' => $payload['rg'],
            'phone' => $payload['phone'],
            'addresses' => [[
                'street' => 'Rua Atualizada',
                'number' => '456',
                'neighborhood' => 'Novo Bairro',
                'city' => 'Nova Cidade',
                'state' => 'SP',
                'zip_code' => '01002-000',
                'complement' => 'Apto 2'
            ]]
        ];

        $response = $this->clientService->updateClient($createdId, $updateData);
        $this->assertEquals('Cliente atualizado com sucesso.', $response['message']);

        $this->clientService->deleteClient($createdId);
    }

    public function testDeleteClientRemovesData(): void
    {
        $payload = [
            'name' => 'Cliente Para Excluir',
            'birthdate' => '1991-01-01',
            'cpf' => '12345678901',
            'rg' => uniqid('RG'),
            'phone' => '11988888888',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]  
        ];

        $response = $this->clientService->createClient($payload);
        $createdId = $response['id'];  
        $response = $this->clientService->deleteClient($createdId); 
        $this->assertEquals('Cliente excluído com sucesso.', $response['message']);
    }

    public function testCreateClientWithAddresses(): void
    {
        $payload = [
            'name' => 'Teste Usuário',
            'birthdate' => '1990-05-05',
            'cpf' => '12345678901', 
            'rg' => uniqid('RG'),
            'phone' => '11999999999',
            'addresses' => [
                [
                    'street' => 'Rua Teste',
                    'number' => '10',
                    'neighborhood' => 'Centro',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zip_code' => '01000-000',
                    'complement' => 'Apto 1'
                ]
            ]
        ];

        $response = $this->clientService->createClient($payload);
        $this->assertArrayHasKey('id', $response);  
        $this->assertEquals('Cliente criado com sucesso.', $response['message']);  
    }

    public function testCreateClientWithInvalidformatCpf(): void
    {
        $clientShortCpf = [
            'name' => 'Teste CPF Curto',
            'birthdate' => '1990-01-01',
            'cpf' => '123456789', 
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        try {
            $this->clientService->createClient($clientShortCpf);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("CPF inválido.", $e->getMessage());
        }

        $clientLongCpf = [
            'name' => 'Teste CPF Longo',
            'birthdate' => '1990-01-01',
            'cpf' => '1234567890123', 
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        try {
            $this->clientService->createClient($clientLongCpf);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("CPF inválido.", $e->getMessage());
        }

        $clientValidCpf = [
            'name' => 'Teste CPF Válido',
            'birthdate' => '1990-01-01',
            'cpf' => '12345678901',
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        $response = $this->clientService->createClient($clientValidCpf);
        $this->assertArrayHasKey('id', $response);
        $this->assertEquals('Cliente criado com sucesso.', $response['message']);
    }

        public function testCreateClientWithMissingFields(): void
    {
        $clientMissingName = [
            'birthdate' => '1990-01-01',
            'cpf' => '12345678901', 
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];
    
        try {
            $this->clientService->createClient($clientMissingName);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("O nome deve ter entre 3 e 100 caracteres.", $e->getMessage()); 
        }

        $clientMissingCpf = [
            'name' => 'Cliente Sem CPF',
            'birthdate' => '1990-01-01',
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        try {
            $this->clientService->createClient($clientMissingCpf);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("CPF inválido.", $e->getMessage());
        }

        $clientMissingRg = [
            'name' => 'Cliente Sem RG',
            'birthdate' => '1990-01-01',
            'cpf' => '12345678901', 
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];

        try {
            $this->clientService->createClient($clientMissingRg);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("RG deve ter entre 5 e 20 caracteres.", $e->getMessage());
        }
    } 

    public function testCreateClientWithInvalidCpfRgCep(): void
    {
        $clientInvalidRg = [
            'name' => 'Cliente RG Inválido',
            'birthdate' => '1990-01-01',
            'cpf' => '12345678901',
            'rg' => 'abc', 
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => '01001-000',
                'complement' => ''
            ]]
        ];
    
        try {
            $this->clientService->createClient($clientInvalidRg);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("RG deve ter entre 5 e 20 caracteres.", $e->getMessage()); 
        }
    
        $clientInvalidCep = [
            'name' => 'Cliente CEP Inválido',
            'birthdate' => '1990-01-01',
            'cpf' => '12345678901', 
            'rg' => '1234567',
            'phone' => '11999999999',
            'addresses' => [[
                'street' => 'Rua Teste',
                'number' => '123',
                'neighborhood' => 'Bairro',
                'city' => 'Cidade',
                'state' => 'SP',
                'zip_code' => 'invalid-cep',
                'complement' => ''
            ]]
        ];
    
        try {
            $this->clientService->createClient($clientInvalidCep);
            $this->fail("Expected InvalidArgumentException was not thrown.");
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("CEP deve ter no máximo 10 caracteres.", $e->getMessage());
        }
    }

}
