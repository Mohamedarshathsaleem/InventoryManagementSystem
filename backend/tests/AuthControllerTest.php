<?php

declare(strict_types=1);

namespace Tests;

use App\Controllers\AuthController;
use App\db;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;

final class AuthControllerTest extends TestCase
{
    private const SECRET = 'test-secret-that-is-at-least-32-characters';

    private db $db;
    private AuthController $auth;

    protected function setUp(): void
    {
        $this->db = TestDatabase::create();
        $this->auth = new AuthController($this->db, self::SECRET);
    }

    private function registerAlice(array $extra = []): array
    {
        return $this->auth->register(array_merge([
            'username' => 'alice',
            'password' => 'correct-horse',
            'email' => 'alice@example.com',
            'full_name' => 'Alice Tan',
        ], $extra));
    }

    public function testRegisterStoresBcryptHashNotPlainText(): void
    {
        $this->registerAlice();

        $stored = $this->db->getPDO()->query("SELECT password FROM users WHERE username = 'alice'")->fetchColumn();

        $this->assertNotSame('correct-horse', $stored);
        $this->assertTrue(password_verify('correct-horse', $stored));
    }

    public function testRegisterIgnoresClientSuppliedRole(): void
    {
        $this->registerAlice(['role' => 'admin']);

        $role = $this->db->getPDO()->query("SELECT role FROM users WHERE username = 'alice'")->fetchColumn();

        $this->assertSame('staff', $role);
    }

    public function testRegisterRejectsDuplicateUsername(): void
    {
        $this->registerAlice();

        $this->assertSame(['error' => 'Username already exists.'], $this->registerAlice());
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $result = $this->registerAlice(['password' => 'short']);

        $this->assertArrayHasKey('error', $result);
    }

    public function testLoginReturnsSignedJwtWithRole(): void
    {
        $this->registerAlice();

        $result = $this->auth->login(['username' => 'alice', 'password' => 'correct-horse']);

        $claims = JWT::decode($result['token'], new Key(self::SECRET, 'HS256'));
        $this->assertSame('alice', $claims->username);
        $this->assertSame('staff', $claims->role);
        $this->assertGreaterThan(time(), $claims->exp);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $this->registerAlice();

        $result = $this->auth->login(['username' => 'alice', 'password' => 'wrong-password']);

        $this->assertSame(['error' => 'Invalid username or password.'], $result);
    }

    public function testLegacyPlainTextPasswordIsUpgradedOnLogin(): void
    {
        $this->db->getPDO()->exec(
            "INSERT INTO users (username, password, email, role, full_name, created_at, updated_at)
             VALUES ('legacy', 'old-plain-pass', 'l@example.com', 'staff', '', '2025-01-01', '2025-01-01')"
        );

        $result = $this->auth->login(['username' => 'legacy', 'password' => 'old-plain-pass']);
        $stored = $this->db->getPDO()->query("SELECT password FROM users WHERE username = 'legacy'")->fetchColumn();

        $this->assertArrayHasKey('token', $result);
        $this->assertTrue(password_verify('old-plain-pass', $stored));
    }
}
