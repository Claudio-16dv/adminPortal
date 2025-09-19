<?php

declare(strict_types=1);

namespace Tests\Unit\AuthService;

use App\Models\Users\Users;
use App\Services\Auth\AuthService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(AuthService::class)]
final class CreateUserEmailExistsTest extends TestCase
{
    public function testCreateUserThrowsWhenEmailAlreadyExists(): void
    {
        $payload = ['name' => 'Gabriel', 'email' => 'gabriel@example.com', 'password' => 'Abc!234'];

        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->once())
            ->method('emailExists')
            ->with('gabriel@example.com')
            ->willReturn(true);

        $usersMock->expects($this->never())->method('createUser');

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O e-mail informado já está cadastrado.');

        $service->createUser($payload);
    }
}
