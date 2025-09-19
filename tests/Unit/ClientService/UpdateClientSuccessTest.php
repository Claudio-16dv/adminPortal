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
final class UpdateClientSuccessTest extends TestCase
{
    #[DataProvider('provideSuccessPayloads')]
    public function testUpdateClientUpdatesDataAndReturnsSuccessMessage(
        int $id,
        array $existingClient,
        array $updateData,
        bool $expectsAddressUpdate
    ): void {
        $clientsMock = $this->createMock(ClientsModel::class);

        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn($existingClient);

        $clientsMock->expects($this->once())
            ->method('update')
            ->with(
                $this->identicalTo($id),
                $this->callback(function (array $data) use ($updateData): bool {
                    return isset($data['name'], $data['birthdate'], $data['cpf'], $data['rg'], $data['phone'])
                        && $data['name']      === $updateData['name']
                        && $data['birthdate'] === $updateData['birthdate']
                        && $data['cpf']       === $updateData['cpf']
                        && $data['rg']        === $updateData['rg']
                        && $data['phone']     === $updateData['phone']
                        && !isset($data['addresses']);
                })
            )
            ->willReturn(true);

        $addressesMock = $this->createMock(AddressesModel::class);

        if ($expectsAddressUpdate) {
            $addressesMock->expects($this->once())
                ->method('updateAddresses')
                ->with(
                    $this->identicalTo($id),
                    $this->callback(function (array $addresses) use ($updateData): bool {
                        $exp = $updateData['addresses'][0];
                        $got = $addresses[0] ?? [];
                        return isset(
                            $got['street'],
                            $got['number'],
                            $got['neighborhood'],
                            $got['city'],
                            $got['state'],
                            $got['zip_code'],
                            $got['complement']
                        )
                        && $got['street']       === $exp['street']
                        && $got['number']       === $exp['number']
                        && $got['neighborhood'] === $exp['neighborhood']
                        && $got['city']         === $exp['city']
                        && $got['state']        === $exp['state']
                        && $got['zip_code']     === $exp['zip_code']
                        && $got['complement']   === $exp['complement'];
                    })
                );
        } else {
            $addressesMock->expects($this->never())->method('updateAddresses');
        }

        $service = new ClientService();
        $ref = new ReflectionClass($service);

        $clientModelProperty = $ref->getProperty('clientModel');
        $clientModelProperty->setAccessible(true);
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $ref->getProperty('addressModel');
        $addressModelProperty->setAccessible(true);
        $addressModelProperty->setValue($service, $addressesMock);

        $response = $service->updateClient($id, $updateData);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('message', $response);
        $this->assertSame('Cliente atualizado com sucesso.', $response['message']);
    }

    public static function provideSuccessPayloads(): array
    {
        $existing = [
            'name'      => 'Cliente Original',
            'birthdate' => '1992-04-04',
            'cpf'       => '52998224725',
            'rg'        => 'RG12345',
            'phone'     => '11988887777',
        ];

        return [
            'update sem addresses' => [
                321,
                ['id' => 321] + $existing,
                [
                    'name'      => 'Cliente Atualizado',
                    'birthdate' => '1992-04-04',
                    'cpf'       => '52998224725',
                    'rg'        => 'RG12345',
                    'phone'     => '11988887777',
                ],
                false,
            ],
            'update com 1 address' => [
                322,
                ['id' => 322] + $existing,
                [
                    'name'      => 'Cliente Atualizado',
                    'birthdate' => '1992-04-04',
                    'cpf'       => '52998224725',
                    'rg'        => 'RG12345',
                    'phone'     => '11988887777',
                    'addresses' => [[
                        'street'       => 'Rua Atualizada',
                        'number'       => '456',
                        'neighborhood' => 'Novo Bairro',
                        'city'         => 'Nova Cidade',
                        'state'        => 'SP',
                        'zip_code'     => '01002-000',
                        'complement'   => 'Apto 2',
                    ]],
                ],
                true,
            ],
        ];
    }
}
