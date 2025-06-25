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
        $role = $data['role'] ?? 'staff';
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

        // Insert user with plain password (for your current setup)
        $stmt = $this->db->getPDO()->prepare(
            "INSERT INTO users (username, password, email, role, full_name, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$username, $password, $email, $role, $full_name, $now, $now]);

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

        // Compare plain text password for now
        if (!$user || $password !== $user['password']) {
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
}