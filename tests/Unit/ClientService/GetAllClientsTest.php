<?php

declare(strict_types=1);

namespace Tests\Unit\ClientService;

use App\Models\Clients\Addresses as AddressesModel;
use App\Models\Clients\Clients as ClientsModel;
use App\Services\Clients\ClientService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(ClientService::class)]
final class GetAllClientsTest extends TestCase
{
    #[DataProvider('provideScenarios')]
    public function testGetAllClients(array $clientsFromModel, array $addressesByClient, array $expected): void
    {
        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('getAll')
            ->willReturn($clientsFromModel);

        $addressesMock = $this->createMock(AddressesModel::class);

        if (empty($clientsFromModel)) {
            $addressesMock->expects($this->never())
                ->method('getAddressByClientId');
        } else {
            $addressesMock->expects($this->exactly(count($clientsFromModel)))
                ->method('getAddressByClientId')
                ->willReturnCallback(fn (int $id) => $addressesByClient[$id] ?? []);
        }

        $service = new ClientService();
        $ref = new ReflectionClass($service);

        $clientModelProperty = $ref->getProperty('clientModel');
        $clientModelProperty->setAccessible(true);
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $ref->getProperty('addressModel');
        $addressModelProperty->setAccessible(true);
        $addressModelProperty->setValue($service, $addressesMock);

        $result = $service->getAllClients();
        $this->assertSame($expected, $result);
    }

    public static function provideScenarios(): array
    {
        return [
            'lista vazia' => [
                [],
                [],
                [],
            ],
            'lista com clientes e endereços' => [
                [
                    [
                        'id' => 1,
                        'name' => 'Alice',
                        'birthdate' => '1990-01-15',
                        'cpf' => '52998224725',
                        'rg' => 'RG123',
                        'phone' => '11999990000',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Bob',
                        'birthdate' => '1985-12-03',
                        'cpf' => '39053344705',
                        'rg' => 'RG456',
                        'phone' => '11988887777',
                    ],
                ],
                [
                    1 => [[
                        'street' => 'Rua 1', 'number' => '100', 'neighborhood' => 'Centro',
                        'city' => 'São Paulo', 'state' => 'SP', 'zip_code' => '01000-000', 'complement' => ''
                    ]],
                    2 => [[
                        'street' => 'Rua 2', 'number' => '200', 'neighborhood' => 'Bairro 2',
                        'city' => 'Santos', 'state' => 'SP', 'zip_code' => '11000-000', 'complement' => 'Casa'
                    ]],
                ],
                [
                    [
                        'id' => 1,
                        'name' => 'Alice',
                        'birthdate' => '15/01/1990',
                        'cpf' => '52998224725',
                        'rg' => 'RG123',
                        'phone' => '11999990000',
                        'addresses' => [[
                            'street' => 'Rua 1', 'number' => '100', 'neighborhood' => 'Centro',
                            'city' => 'São Paulo', 'state' => 'SP', 'zip_code' => '01000-000', 'complement' => ''
                        ]],
                    ],
                    [
                        'id' => 2,
                        'name' => 'Bob',
                        'birthdate' => '03/12/1985',
                        'cpf' => '39053344705',
                        'rg' => 'RG456',
                        'phone' => '11988887777',
                        'addresses' => [[
                            'street' => 'Rua 2', 'number' => '200', 'neighborhood' => 'Bairro 2',
                            'city' => 'Santos', 'state' => 'SP', 'zip_code' => '11000-000', 'complement' => 'Casa'
                        ]],
                    ],
                ],
            ],
            'birthdate inválida não formata (mantém string)' => [
                [
                    [
                        'id' => 30,
                        'name' => 'Carlos',
                        'birthdate' => '31-12-1999',
                        'cpf' => '11122233344',
                        'rg' => 'RGX',
                        'phone' => '11911112222',
                    ],
                ],
                [
                    30 => [[
                        'street' => 'Qualquer', 'number' => '1', 'neighborhood' => 'N',
                        'city' => 'Cidade', 'state' => 'SP', 'zip_code' => '01000-000', 'complement' => ''
                    ]],
                ],
                [
                    [
                        'id' => 30,
                        'name' => 'Carlos',
                        'birthdate' => '31-12-1999',
                        'cpf' => '11122233344',
                        'rg' => 'RGX',
                        'phone' => '11911112222',
                        'addresses' => [[
                            'street' => 'Qualquer', 'number' => '1', 'neighborhood' => 'N',
                            'city' => 'Cidade', 'state' => 'SP', 'zip_code' => '01000-000', 'complement' => ''
                        ]],
                    ],
                ],
            ],
        ];
    }
}
