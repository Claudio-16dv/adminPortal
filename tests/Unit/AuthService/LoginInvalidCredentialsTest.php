<?php

declare(strict_types=1);

namespace Tests\Unit\AuthService;

use App\Models\Users\Users;
use App\Services\Auth\AuthService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(AuthService::class)]
final class LoginInvalidCredentialsTest extends TestCase
{
    #[DataProvider('provideInvalidCredentialScenarios')]
    public function testLoginThrowsInvalidCredentials(array|null $userRow, string $password): void
    {
        $payload = ['email' => 'user@ex.com', 'password' => $password];

        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->once())
            ->method('loginUser')
            ->with('user@ex.com')
            ->willReturn($userRow);

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Credenciais inválidas.');

        $service->login($payload);
    }

    public static function provideInvalidCredentialScenarios(): array
    {
        return [
            'usuario inexistente' => [null, 'whatever'],
            'senha incorreta' => [[
                'id' => 10,
                'email' => 'user@ex.com',
                'password' => password_hash('rightpass', PASSWORD_DEFAULT),
            ], 'wrongpass'],
        ];
    }
}
