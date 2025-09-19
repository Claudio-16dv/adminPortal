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
final class LoginEmailValidationErrorsTest extends TestCase
{
    #[DataProvider('provideInvalidEmails')]
    public function testLoginThrowsOnInvalidEmail(string $email, string $expectedMessage): void
    {
        $payload = ['email' => $email, 'password' => 'Abc!234'];

        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->never())->method('loginUser');

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $service->login($payload);
    }

    public static function provideInvalidEmails(): array
    {
        return [
            'email vazio' => ['', 'O campo "email" é obrigatório.'],
            'email longo' => [str_repeat('a', 101) . '@ex.com', 'O e-mail deve ter no máximo 100 caracteres.'],
            'email inválido' => ['invalid', 'O e-mail informado é inválido.'],
        ];
    }
}
