<?php

declare(strict_types=1);

namespace Tests\Unit\ClientService;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use App\Services\Clients\ClientService;
use App\Models\Clients\Clients as ClientsModel;
use App\Models\Clients\Addresses as AddressesModel;
use InvalidArgumentException;
use ReflectionClass;

#[CoversClass(ClientService::class)]
final class CreateClientAddressesRequiredTest extends TestCase
{
    public static function provideInvalidAddressPayloads(): array
    {
        $basePayload = [
            'name'      => 'Fulano',
            'birthdate' => '1991-02-02',
            'cpf'       => '39053344705',
            'rg'        => 'RG7777',
            'phone'     => '11988887777',
        ];

        return [
            'quando a chave addresses esta ausente' => [
                'payload' => $basePayload
            ],
            'quando a chave addresses esta vazia' => [
                'payload' => $basePayload + ['addresses' => []]
            ],
        ];
    }

    #[DataProvider('provideInvalidAddressPayloads')]
    public function testCreateClientThrowsWhenAddressesAreMissingOrEmpty(array $payload): void
    {
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
        $this->expectExceptionMessage('Pelo menos um endereço deve ser informado.');

        $service->createClient($payload);
    }
}
