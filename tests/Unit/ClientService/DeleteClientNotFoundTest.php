<?php

declare(strict_types=1);

namespace Tests\Unit\ClientService;

use PHPUnit\Framework\TestCase;
use App\Services\Clients\ClientService;
use App\Models\Clients\Clients as ClientsModel;
use App\Models\Clients\Addresses as AddressesModel;
use InvalidArgumentException;

final class DeleteClientNotFoundTest extends TestCase
{
    public function testDeleteClient_ThrowsWhenNotFound(): void
    {
        $id = 999;

        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn(null);

        $clientsMock->expects($this->never())
            ->method('delete');

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->never())
            ->method($this->anything());

        $service = new ClientService();

        $ref = new \ReflectionClass($service);
        $clientProp = $ref->getProperty('clientModel');
        $clientProp->setAccessible(true);
        $clientProp->setValue($service, $clientsMock);

        $addrProp = $ref->getProperty('addressModel');
        $addrProp->setAccessible(true);
        $addrProp->setValue($service, $addressesMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cliente não encontrado.');

        $service->deleteClient($id);
    }
}
