<?php

declare(strict_types=1);

namespace Tests\Unit\ClientService;

use App\Models\Clients\Addresses as AddressesModel;
use App\Models\Clients\Clients as ClientsModel;
use App\Services\Clients\ClientService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(ClientService::class)]
final class CreateClientCpfInvalidTest extends TestCase
{
    public static function provideInvalidCpfs(): array
    {
        return [
            'cpf curto (9 digitos)' => ['cpf' => '123456789'],
            'cpf longo (13 digitos)' => ['cpf' => '1234567890123'],
            'cpf com letras' => ['cpf' => 'ABC45678901'],
            'cpf com todos os digitos iguais' => ['cpf' => '11111111111'],
        ];
    }

    #[DataProvider('provideInvalidCpfs')]
    public function testCreateClientThrowsWhenCpfIsInvalid(string $cpf): void
    {
        $payload = [
            'name'      => 'Fulano',
            'birthdate' => '1991-02-02',
            'cpf'       => $cpf,
            'rg'        => 'RG7777',
            'phone'     => '11988887777',
            'addresses' => [[
                'street'       => 'Rua A',
                'number'       => '100',
                'neighborhood' => 'Bairro A',
                'city'         => 'Cidade A',
                'state'        => 'SP',
                'zip_code'     => '01001-000',
                'complement'   => '',
            ]],
        ];

        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->never())->method('create');

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->never())->method('addAddresses');

        $service = new ClientService();
        $reflection = new ReflectionClass($service);

        $clientModelProperty = $reflection->getProperty('clientModel');
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $reflection->getProperty('addressModel');
        $addressModelProperty->setValue($service, $addressesMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CPF inválido.');

        $service->createClient($payload);
    }
}
