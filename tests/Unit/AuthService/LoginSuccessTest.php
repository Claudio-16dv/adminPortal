<?php

declare(strict_types=1);

namespace Tests\Unit\AuthService;

use App\Models\Users\Users;
use App\Services\Auth\AuthService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(AuthService::class)]
final class LoginSuccessTest extends TestCase
{
    public function testLoginReturnsSuccess(): void
    {
        $payload = ['email' => 'user@ex.com', 'password' => 'Secret!2'];
        $user = [
            'id' => 1,
            'email' => 'user@ex.com',
            'password' => password_hash('Secret!2', PASSWORD_DEFAULT),
        ];

        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->once())
            ->method('loginUser')
            ->with('user@ex.com')
            ->willReturn($user);

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $result = $service->login($payload);

        $this->assertSame(['success' => true, 'message' => 'Login efetuado com sucesso.'], $result);
    }
}
