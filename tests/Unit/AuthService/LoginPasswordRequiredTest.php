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
final class LoginPasswordRequiredTest extends TestCase
{
    public function testLoginThrowsWhenPasswordIsEmpty(): void
    {
        $payload = ['email' => 'user@ex.com', 'password' => ''];

        $usersMock = $this->createMock(Users::class);
        $usersMock->expects($this->never())->method('loginUser');

        $service = new AuthService();
        $ref = new ReflectionClass($service);
        $prop = $ref->getProperty('userModel');
        $prop->setAccessible(true);
        $prop->setValue($service, $usersMock);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A senha é obrigatória.');

        $service->login($payload);
    }
}
