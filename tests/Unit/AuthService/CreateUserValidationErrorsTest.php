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
final class CreateUserValidationErrorsTest extends TestCase
{
    #[DataProvider('provideInvalidPayloads')]
    public function testCreateUserThrowsOnValidationErrors(array $payload, string $expectedMessage): void
    {
        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->never())->method('emailExists');
        $usersMock->expects($this->never())->method('createUser');

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        $service->createUser($payload);
    }

    public static function provideInvalidPayloads(): array
    {
        return [
            'campo não permitido' => [
                ['name' => 'Flavio', 'email' => 'a@a.com', 'password' => 'Abc!23', 'role' => 'admin'],
                'Campo não permitido: role',
            ],
            'nome curto' => [
                ['name' => 'Al', 'email' => 'a@a.com', 'password' => 'Abc!23'],
                'O nome deve ter entre 3 e 100 caracteres.',
            ],
            'nome longo' => [
                ['name' => str_repeat('A', 101), 'email' => 'a@a.com', 'password' => 'Abc!23'],
                'O nome deve ter entre 3 e 100 caracteres.',
            ],
            'nome com número' => [
                ['name' => 'Flavio1', 'email' => 'a@a.com', 'password' => 'Abc!23'],
                'O nome não pode conter números.',
            ],
            'email vazio' => [
                ['name' => 'Flavio', 'email' => '', 'password' => 'Abc!23'],
                'O campo "email" é obrigatório.',
            ],
            'email longo' => [
                ['name' => 'Flavio', 'email' => str_repeat('a', 101) . '@ex.com', 'password' => 'Abc!23'],
                'O e-mail deve ter no máximo 100 caracteres.',
            ],
            'email inválido' => [
                ['name' => 'Flavio', 'email' => 'invalid', 'password' => 'Abc!23'],
                'O e-mail informado é inválido.',
            ],
            'password vazio' => [
                ['name' => 'Flavio', 'email' => 'a@a.com', 'password' => ''],
                'O campo "password" é obrigatório.',
            ],
            'password curto' => [
                ['name' => 'Flavio', 'email' => 'a@a.com', 'password' => 'A!23'],
                'A senha deve ter pelo menos 6 caracteres.',
            ],
            'password longo' => [
                ['name' => 'Flavio', 'email' => 'a@a.com', 'password' => str_repeat('A', 256) . '!'],
                'A senha é muito longa.',
            ],
            'password sem maiúscula' => [
                ['name' => 'Flavio', 'email' => 'a@a.com', 'password' => 'abc!234'],
                'A senha deve conter pelo menos uma letra maiúscula e um caractere especial.',
            ],
            'password sem especial' => [
                ['name' => 'Flavio', 'email' => 'a@a.com', 'password' => 'Abc1234'],
                'A senha deve conter pelo menos uma letra maiúscula e um caractere especial.',
            ],
        ];
    }
}
