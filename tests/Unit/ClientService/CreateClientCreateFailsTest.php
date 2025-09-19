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
final class CreateClientCreateFailsTest extends TestCase
{
    public static function providePayloadsThatShouldFailInCreate(): array
    {
        $basePayload = [
            'name'      => 'Fulano',
            'birthdate' => '1991-02-02',
            'cpf'       => '39053344705',
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

        return [
            'payload padrao' => ['payload' => $basePayload],
            'payload alternativo' => ['payload' => $basePayload + [
                'name' => 'Beltrano',
                'cpf'  => '52998224725',
            ]],
        ];
    }

    #[DataProvider('providePayloadsThatShouldFailInCreate')]
    public function testCreateClientThrowsWhenClientModelCreateFails(array $payload): void
    {
        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('create')
            ->with($this->isType('array'))
            ->willReturn(0);

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->never())->method('addAddresses');

        $service = new ClientService();
        $reflection = new ReflectionClass($service);

        $clientModelProperty = $reflection->getProperty('clientModel');
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $reflection->getProperty('addressModel');
        $addressModelProperty->setValue($service, $addressesMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Erro ao cadastrar cliente.');

        $service->createClient($payload);
    }
}
