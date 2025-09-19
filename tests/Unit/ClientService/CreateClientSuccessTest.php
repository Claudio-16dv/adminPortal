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
final class CreateClientSuccessTest extends TestCase
{
    #[DataProvider('provideValidPayloads')]
    public function testCreateClientCreatesAndReturnsIdAndMessage(array $payload, int $generatedId): void
    {
        $clientsMock = $this->createMock(ClientsModel::class);
        $clientsMock->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $data) use ($payload): bool {
                $keys = ['name','birthdate','cpf','rg','phone','addresses'];
                foreach ($keys as $k) {
                    if (!isset($data[$k])) {
                        return false;
                    }
                }
                return $data['name']      === $payload['name']
                    && $data['birthdate'] === $payload['birthdate']
                    && $data['cpf']       === $payload['cpf']
                    && $data['rg']        === $payload['rg']
                    && $data['phone']     === $payload['phone']
                    && is_array($data['addresses']);
            }))
            ->willReturn($generatedId);

        $addressesMock = $this->createMock(AddressesModel::class);
        $addressesMock->expects($this->once())
            ->method('addAddresses')
            ->with(
                $this->identicalTo($generatedId),
                $this->callback(function (array $addresses) use ($payload): bool {
                    $exp = $payload['addresses'][0];
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

        $service = new ClientService();
        $ref = new ReflectionClass($service);

        $clientModelProperty = $ref->getProperty('clientModel');
        $clientModelProperty->setAccessible(true);
        $clientModelProperty->setValue($service, $clientsMock);

        $addressModelProperty = $ref->getProperty('addressModel');
        $addressModelProperty->setAccessible(true);
        $addressModelProperty->setValue($service, $addressesMock);

        $response = $service->createClient($payload);

        $this->assertIsArray($response);
        $this->assertSame('Cliente criado com sucesso.', $response['message']);
        $this->assertSame($generatedId, $response['id']);
    }

    public static function provideValidPayloads(): array
    {
        return [
            'com 1 address' => [
                [
                    'name'      => 'Teste Usuário',
                    'birthdate' => '1990-05-05',
                    'cpf'       => '52998224725',
                    'rg'        => 'RG12345',
                    'phone'     => '11999999999',
                    'addresses' => [[
                        'street'       => 'Rua Teste',
                        'number'       => '10',
                        'neighborhood' => 'Centro',
                        'city'         => 'São Paulo',
                        'state'        => 'SP',
                        'zip_code'     => '01000-000',
                        'complement'   => 'Apto 1',
                    ]],
                ],
                222,
            ],
        ];
    }
}
