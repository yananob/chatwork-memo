<?php

declare(strict_types=1);

namespace Tests;

use App\Security;
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $_SESSION = [];
    }

    public function testGenerateTokenCreatesValidToken(): void
    {
        $token = Security::generateToken();
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));
        $this->assertSame($token, $_SESSION['csrf_token'] ?? null);
    }

    public function testValidateTokenReturnsTrueForValidToken(): void
    {
        $token = Security::generateToken();
        $this->assertTrue(Security::validateToken($token));
    }

    public function testValidateTokenReturnsFalseForInvalidToken(): void
    {
        Security::generateToken();
        $this->assertFalse(Security::validateToken('invalid_token'));
        $this->assertFalse(Security::validateToken(null));
        $this->assertFalse(Security::validateToken(''));
    }

    public function testHtmlEscaping(): void
    {
        $input = '<script>alert("xss")</script>';
        $expected = '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;';
        $this->assertSame($expected, Security::h($input));
    }
}
