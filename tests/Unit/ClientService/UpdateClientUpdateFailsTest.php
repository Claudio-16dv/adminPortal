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
final class UpdateClientUpdateFailsTest extends TestCase
{
    #[DataProvider('provideFailingPayloads')]
    public function testUpdateClientThrowsWhenUpdateReturnsFalse(
        int $id,
        array $existingClient,
        array $updateData
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
                $this->callback(fn (array $data) =>
                    isset($data['name'], $data['birthdate'], $data['cpf'], $data['rg'], $data['phone'])
                    && !isset($data['addresses'])
                )
            )
            ->willReturn(false);

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->never())->method($this->anything());

        $service = new ClientService();
        $ref = new ReflectionClass($service);

        $clientModelProperty = $ref->getProperty('clientModel');
        $clientModelProperty->setAccessible(true);
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $ref->getProperty('addressModel');
        $addressModelProperty->setAccessible(true);
        $addressModelProperty->setValue($service, $addressesMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Erro ao atualizar cliente.');

        $service->updateClient($id, $updateData);
    }

    public static function provideFailingPayloads(): array
    {
        $existing = [
            'name'      => 'Cliente Original',
            'birthdate' => '1992-04-04',
            'cpf'       => '52998224725',
            'rg'        => 'RG12345',
            'phone'     => '11988887777',
        ];

        return [
            'falha sem addresses' => [
                500,
                ['id' => 500] + $existing,
                [
                    'name'      => 'Cliente Nao Atualiza',
                    'birthdate' => '1992-04-04',
                    'cpf'       => '52998224725',
                    'rg'        => 'RG12345',
                    'phone'     => '11988887777',
                ],
            ],
            'falha com addresses (ainda assim update() falha antes)' => [
                501,
                ['id' => 501] + $existing,
                [
                    'name'      => 'Cliente Nao Atualiza',
                    'birthdate' => '1992-04-04',
                    'cpf'       => '52998224725',
                    'rg'        => 'RG12345',
                    'phone'     => '11988887777',
                    'addresses' => [[
                        'street'       => 'Rua X',
                        'number'       => '1',
                        'neighborhood' => 'Y',
                        'city'         => 'Z',
                        'state'        => 'SP',
                        'zip_code'     => '01000-000',
                        'complement'   => '',
                    ]],
                ],
            ],
        ];
    }
}
