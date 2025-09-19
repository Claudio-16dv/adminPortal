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
final class UpdateClientNotFoundTest extends TestCase
{
    #[DataProvider('provideIdsAndPayload')]
    public function testUpdateClientThrowsWhenNotFound(int $id, array $updateData): void
    {
        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(null);
        $clientsMock->expects($this->never())->method('update');

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
        $this->expectExceptionMessage('Cliente não encontrado.');

        $service->updateClient($id, $updateData);
    }

    public static function provideIdsAndPayload(): array
    {
        $payloadBase = [
            'name'      => 'Qualquer',
            'birthdate' => '1990-01-01',
            'cpf'       => '52998224725',
            'rg'        => 'RG123',
            'phone'     => '11999990000',
        ];

        return [
            'id 0' => [0, $payloadBase],
            'id 999' => [999, $payloadBase],
        ];
    }
}
