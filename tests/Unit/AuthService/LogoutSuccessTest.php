<?php

declare(strict_types=1);

namespace Tests\Unit\AuthService;

use App\Services\Auth\AuthService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthService::class)]
final class LogoutSuccessTest extends TestCase
{
    public function testLogoutReturnsSuccess(): void
    {
        $service = new AuthService();
        $result = $service->logout();
        $this->assertSame(['success' => true, 'message' => 'Logout efetuado com sucesso.'], $result);
    }
}
