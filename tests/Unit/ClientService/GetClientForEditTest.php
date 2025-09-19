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
final class GetClientForEditTest extends TestCase
{
    #[DataProvider('provideClientsForEdit')]
    public function testGetClientForEditReturnsCorrectClientWithModelMocks(
        int $id,
        array $clientFromModel,
        array $addressesFromModel,
        array $expected
    ): void {
        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn($clientFromModel);

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->once())
            ->method('getAddressByClientId')
            ->with($id)
            ->willReturn($addressesFromModel);

        $service = new ClientService();
        $ref = new ReflectionClass($service);

        $clientModelProperty = $ref->getProperty('clientModel');
        $clientModelProperty->setAccessible(true);
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $ref->getProperty('addressModel');
        $addressModelProperty->setAccessible(true);
        $addressModelProperty->setValue($service, $addressesMock);

        $result = $service->getClientForEdit($id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('cpf', $result);
        $this->assertArrayHasKey('addresses', $result);

        $this->assertSame($expected['id'], $result['id']);
        $this->assertSame($expected['name'], $result['name']);
        $this->assertSame($expected['cpf'], $result['cpf']);

        $this->assertIsArray($result['addresses']);
        $this->assertCount(count($expected['addresses']), $result['addresses']);

        foreach ($expected['addresses'] as $i => $addr) {
            $this->assertArrayHasKey($i, $result['addresses']);
            $this->assertSame($addr['street'], $result['addresses'][$i]['street'] ?? null);
            $this->assertSame($addr['number'], $result['addresses'][$i]['number'] ?? null);
            $this->assertSame($addr['neighborhood'], $result['addresses'][$i]['neighborhood'] ?? null);
            $this->assertSame($addr['city'], $result['addresses'][$i]['city'] ?? null);
            $this->assertSame($addr['state'], $result['addresses'][$i]['state'] ?? null);
            $this->assertSame($addr['zip_code'], $result['addresses'][$i]['zip_code'] ?? null);
            $this->assertSame($addr['complement'], $result['addresses'][$i]['complement'] ?? null);
        }
    }

    public static function provideClientsForEdit(): array
    {
        return [
            'cliente com 1 endereço' => [
                101,
                [
                    'id'        => 101,
                    'name'      => 'Cliente Original',
                    'birthdate' => '1992-04-04',
                    'cpf'       => '52998224725',
                    'rg'        => 'RG12345',
                    'phone'     => '11988887777',
                ],
                [[
                    'street'       => 'Rua Teste',
                    'number'       => '123',
                    'neighborhood' => 'Bairro',
                    'city'         => 'Cidade',
                    'state'        => 'SP',
                    'zip_code'     => '01001-000',
                    'complement'   => ''
                ]],
                [
                    'id'   => 101,
                    'name' => 'Cliente Original',
                    'cpf'  => '52998224725',
                    'addresses' => [[
                        'street'       => 'Rua Teste',
                        'number'       => '123',
                        'neighborhood' => 'Bairro',
                        'city'         => 'Cidade',
                        'state'        => 'SP',
                        'zip_code'     => '01001-000',
                        'complement'   => ''
                    ]]
                ],
            ],
            'cliente com 2 endereços' => [
                202,
                [
                    'id'        => 202,
                    'name'      => 'Cliente Dois Endereços',
                    'birthdate' => '1990-01-01',
                    'cpf'       => '39053344705',
                    'rg'        => 'RG67890',
                    'phone'     => '11999990000',
                ],
                [
                    [
                        'street'       => 'Rua A',
                        'number'       => '10',
                        'neighborhood' => 'Centro',
                        'city'         => 'São Paulo',
                        'state'        => 'SP',
                        'zip_code'     => '01000-000',
                        'complement'   => 'Apto 1'
                    ],
                    [
                        'street'       => 'Rua B',
                        'number'       => '200',
                        'neighborhood' => 'Bairro B',
                        'city'         => 'Santos',
                        'state'        => 'SP',
                        'zip_code'     => '11000-000',
                        'complement'   => ''
                    ]
                ],
                [
                    'id'   => 202,
                    'name' => 'Cliente Dois Endereços',
                    'cpf'  => '39053344705',
                    'addresses' => [
                        [
                            'street'       => 'Rua A',
                            'number'       => '10',
                            'neighborhood' => 'Centro',
                            'city'         => 'São Paulo',
                            'state'        => 'SP',
                            'zip_code'     => '01000-000',
                            'complement'   => 'Apto 1'
                        ],
                        [
                            'street'       => 'Rua B',
                            'number'       => '200',
                            'neighborhood' => 'Bairro B',
                            'city'         => 'Santos',
                            'state'        => 'SP',
                            'zip_code'     => '11000-000',
                            'complement'   => ''
                        ]
                    ]
                ],
            ],
        ];
    }
}
