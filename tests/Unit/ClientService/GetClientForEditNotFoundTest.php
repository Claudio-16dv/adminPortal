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
final class GetClientForEditNotFoundTest extends TestCase
{
    #[DataProvider('provideIdsNotFound')]
    public function testGetClientForEditThrowsWhenNotFound(int $id): void
    {
        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(null);

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->never())
            ->method('getAddressByClientId');

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

        $service->getClientForEdit($id);
    }

    public static function provideIdsNotFound(): array
    {
        return [
            'id 0' => [0],
            'id 999' => [999],
        ];
    }
}
