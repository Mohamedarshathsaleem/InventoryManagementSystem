<?php
namespace App\Controllers;

use App\db;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $db;
    private $secretKey;

    public function __construct(db $db, $secretKey)
    {
        $this->db = $db;
        $this->secretKey = $secretKey;
    }

    public function register($data)
    {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        $email = $data['email'] ?? '';
        // Never trust a client-supplied role: every self-registered account is staff.
        // Admins are promoted directly in the database (see database/schema.sql).
        $role = 'staff';
        $full_name = $data['full_name'] ?? '';
        $now = date('Y-m-d H:i:s');

        if (!$username || !$password || !$email) {
            return ['error' => 'Username, password, and email are required.'];
        }

        // Check if user exists
        $stmt = $this->db->getPDO()->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return ['error' => 'Username already exists.'];
        }

        if (strlen($password) < 8) {
            return ['error' => 'Password must be at least 8 characters.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->getPDO()->prepare(
            "INSERT INTO users (username, password, email, role, full_name, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$username, $hash, $email, $role, $full_name, $now, $now]);

        return ['message' => 'User registered successfully.'];
    }

    public function login($data)
    {
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if (!$username || !$password) {
            return ['error' => 'Username and password are required.'];
        }

        $stmt = $this->db->getPDO()->prepare("SELECT id, username, password, email, role, full_name FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || !$this->verifyAndUpgrade($user, $password)) {
            return ['error' => 'Invalid username or password.'];
        }

        // Generate JWT
        $payload = [
            'sub' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'iat' => time(),
            'exp' => time() + 3600 // 1 hour
        ];
        $token = JWT::encode($payload, $this->secretKey, 'HS256');

        return [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role'],
                'full_name' => $user['full_name']
            ]
        ];
    }

    /**
     * Verifies a password against its bcrypt hash. Accounts created before hashing was
     * introduced still hold plain text; on their first successful login the password is
     * re-hashed in place, so old data migrates without a manual reset.
     */
    private function verifyAndUpgrade(array $user, string $password): bool
    {
        $stored = (string) $user['password'];
        $info = password_get_info($stored);

        if ($info['algo'] !== null && $info['algo'] !== 0) {
            if (!password_verify($password, $stored)) {
                return false;
            }
            if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $this->storeHash((int) $user['id'], $password);
            }
            return true;
        }

        if (hash_equals($stored, $password)) {
            $this->storeHash((int) $user['id'], $password);
            return true;
        }

        return false;
    }

    private function storeHash(int $id, string $password): void
    {
        $stmt = $this->db->getPDO()->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }
}
