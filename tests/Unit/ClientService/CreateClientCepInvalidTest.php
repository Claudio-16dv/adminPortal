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
final class CreateClientCepInvalidTest extends TestCase
{
    public static function provideInvalidCeps(): array
    {
        return [
            'cep com 11 caracteres' => ['zip' => '12345-67890'],
            'cep com 12 caracteres' => ['zip' => 'ABCDEF-12345'],
            'cep muito longo (20 caracteres)' => ['zip' => str_repeat('9', 20)],
        ];
    }

    #[DataProvider('provideInvalidCeps')]
    public function testCreateClientThrowsWhenZipCodeIsTooLong(string $zip): void
    {
        $payload = [
            'name'      => 'Fulano',
            'birthdate' => '1991-02-02',
            'cpf'       => '52998224725',
            'rg'        => 'RG7777',
            'phone'     => '11988887777',
            'addresses' => [[
                'street'       => 'Rua A',
                'number'       => '100',
                'neighborhood' => 'Bairro A',
                'city'         => 'Cidade A',
                'state'        => 'SP',
                'zip_code'     => $zip,
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
        $this->expectExceptionMessage('CEP deve ter no máximo 10 caracteres.');

        $service->createClient($payload);
    }
}
