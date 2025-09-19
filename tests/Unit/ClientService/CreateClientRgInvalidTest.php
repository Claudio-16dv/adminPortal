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
final class CreateClientRgInvalidTest extends TestCase
{
    public static function provideInvalidRgs(): array
    {
        return [
            'rg curto (3 caracteres)' => ['rg' => 'RG1'],
            'rg muito curto (4 caracteres)' => ['rg' => '1234'],
            'rg muito longo (21 caracteres)' => ['rg' => str_repeat('A', 21)],
            'rg super longo (30 caracteres)' => ['rg' => str_repeat('9', 30)],
        ];
    }

    #[DataProvider('provideInvalidRgs')]
    public function testCreateClientThrowsWhenRgIsInvalid(string $rg): void
    {
        $payload = [
            'name'      => 'Fulano',
            'birthdate' => '1991-02-02',
            'cpf'       => '39053344705',
            'rg'        => $rg,
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
        $this->expectExceptionMessage('RG deve ter entre 5 e 20 caracteres.');

        $service->createClient($payload);
    }
}
