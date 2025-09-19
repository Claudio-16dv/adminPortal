<?php

declare(strict_types=1);

namespace Tests\Unit\ClientService;

use PHPUnit\Framework\TestCase;
use App\Services\Clients\ClientService;
use App\Models\Clients\Clients as ClientsModel;
use App\Models\Clients\Addresses as AddressesModel;

final class GetClientForEditNoAddressesTest extends TestCase
{
    public function testGetClientForEdit_WhenNoAddresses_ReturnsEmptyAddressesArray(): void
    {
        $id = 777;

        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('findById')
            ->with($id)
            ->willReturn([
                'id'        => $id,
                'name'      => 'Cliente Sem Endereço',
                'birthdate' => '1990-01-01',
                'cpf'       => '52998224725',
                'rg'        => 'RG7777',
                'phone'     => '11999999999',
            ]);

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->once())
            ->method('getAddressByClientId')
            ->with($id)
            ->willReturn([]); 

        $service = new ClientService();
        $ref = new \ReflectionClass($service);
        $cp = $ref->getProperty('clientModel');  $cp->setAccessible(true);  $cp->setValue($service, $clientsMock);
        $ap = $ref->getProperty('addressModel'); $ap->setAccessible(true);  $ap->setValue($service, $addressesMock);

        $result = $service->getClientForEdit($id);

        $this->assertIsArray($result);
        $this->assertSame($id, $result['id']);
        $this->assertArrayHasKey('addresses', $result);
        $this->assertSame([], $result['addresses']);
    }
}
