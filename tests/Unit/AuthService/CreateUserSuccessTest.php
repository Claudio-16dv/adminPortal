<?php

declare(strict_types=1);

namespace Tests\Unit\AuthService;

use App\Models\Users\Users;
use App\Services\Auth\AuthService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(AuthService::class)]
final class CreateUserSuccessTest extends TestCase
{
    public function testCreateUserCreatesAndReturnsIdAndMessage(): void
    {
        $payload = ['name' => 'Gabriel', 'email' => 'gabriel@example.com', 'password' => 'SecreT!2'];

        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->once())
            ->method('emailExists')
            ->with('gabriel@example.com')
            ->willReturn(false);

        $usersMock->expects($this->once())
            ->method('createUser')
            ->with($this->callback(function (array $data): bool {
                return $data['name'] === 'Gabriel'
                    && $data['email'] === 'gabriel@example.com'
                    && isset($data['password'])
                    && password_verify('SecreT!2', $data['password']);
            }))
            ->willReturn(77);

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $response = $service->createUser($payload);

        $this->assertSame(['message' => 'Usuário criado com sucesso', 'id' => 77], $response);
    }
}
