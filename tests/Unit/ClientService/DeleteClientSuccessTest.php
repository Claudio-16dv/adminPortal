<?php

declare(strict_types=1);

namespace Tests\Unit\ClientService;

use PHPUnit\Framework\TestCase;
use App\Services\Clients\ClientService;
use App\Models\Clients\Clients as ClientsModel;
use App\Models\Clients\Addresses as AddressesModel;

final class DeleteClientSuccessTest extends TestCase
{
    public function testDeleteClient_RemovesAndReturnsSuccessMessage(): void
    {
        $id = 123;

        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn([
                'id'        => $id,
                'name'      => 'Cliente Para Excluir',
                'birthdate' => '1991-01-01',
                'cpf'       => '52998224725',
                'rg'        => 'RG123456',
                'phone'     => '11999999999',
            ]);

        $clientsMock->expects($this->once())
            ->method('delete')
            ->with($id)
            ->willReturn(true);

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

        $response = $service->deleteClient($id);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('message', $response);
        $this->assertSame('Cliente excluído com sucesso.', $response['message']);
    }
}
