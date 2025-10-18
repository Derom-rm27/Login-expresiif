<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

final class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function findByEmail(string $email): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute([':email' => $email]);
        $user = $statement->fetch();

        return $this->transform($user ?: null);
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $user = $statement->fetch();

        return $this->transform($user ?: null);
    }

    /**
     * @return array<int, array>
     */
    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM users ORDER BY created_at DESC');
        $users = $statement->fetchAll();

        return array_map(fn ($user) => $this->transform($user), $users);
    }

    public function create(array $data): array
    {
        $statement = $this->db->prepare('INSERT INTO users (username, email, password, roles, created_at, updated_at)
            VALUES (:username, :email, :password, :roles, :created_at, :updated_at)');

        $now = date('Y-m-d H:i:s');

        $statement->execute([
            ':username' => $data['username'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':roles' => json_encode($data['roles'] ?? ['ROLE_USER'], JSON_THROW_ON_ERROR),
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    public function updateUsername(int $id, string $username): void
    {
        $statement = $this->db->prepare('UPDATE users SET username = :username, updated_at = :updated_at WHERE id = :id');
        $statement->execute([
            ':username' => $username,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    public function updateRoles(int $id, array $roles): void
    {
        $statement = $this->db->prepare('UPDATE users SET roles = :roles, updated_at = :updated_at WHERE id = :id');
        $statement->execute([
            ':roles' => json_encode($roles, JSON_THROW_ON_ERROR),
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    public function markEmailVerified(int $id): void
    {
        $statement = $this->db->prepare('UPDATE users SET email_verified_at = :verified_at, updated_at = :updated_at WHERE id = :id');
        $now = date('Y-m-d H:i:s');
        $statement->execute([
            ':verified_at' => $now,
            ':updated_at' => $now,
            ':id' => $id,
        ]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $statement = $this->db->prepare('UPDATE users SET password = :password, updated_at = :updated_at WHERE id = :id');
        $statement->execute([
            ':password' => $hash,
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    private function transform(?array $user): ?array
    {
        if ($user === null) {
            return null;
        }

        $user['roles'] = json_decode($user['roles'], true, 512, JSON_THROW_ON_ERROR);
        $user['email_verified_at'] = $user['email_verified_at'] ?: null;
        return $user;
    }
}