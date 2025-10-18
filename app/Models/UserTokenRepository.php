<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use DateTimeImmutable;
use PDO;

final class UserTokenRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function create(int $userId, string $type, array $payload, DateTimeImmutable $expiresAt): string
    {
        $this->purgeByType($userId, $type);

        $token = bin2hex(random_bytes(32));
        $statement = $this->db->prepare('INSERT INTO user_tokens (user_id, token, type, payload, expires_at, created_at)
            VALUES (:user_id, :token, :type, :payload, :expires_at, :created_at)');

        $statement->execute([
            ':user_id' => $userId,
            ':token' => $token,
            ':type' => $type,
            ':payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            ':expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            ':created_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    public function consume(string $token, string $type): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM user_tokens WHERE token = :token AND type = :type LIMIT 1');
        $statement->execute([
            ':token' => $token,
            ':type' => $type,
        ]);

        $record = $statement->fetch();

        if (!$record) {
            return null;
        }

        $this->deleteById((int) $record['id']);

        $expiresAt = new DateTimeImmutable($record['expires_at']);
        if ($expiresAt < new DateTimeImmutable()) {
            return null;
        }

        $record['payload'] = $record['payload'] !== null
            ? json_decode($record['payload'], true, 512, JSON_THROW_ON_ERROR)
            : [];

        return $record;
    }

    public function purgeByType(int $userId, string $type): void
    {
        $statement = $this->db->prepare('DELETE FROM user_tokens WHERE user_id = :user_id AND type = :type');
        $statement->execute([
            ':user_id' => $userId,
            ':type' => $type,
        ]);
    }

    private function deleteById(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM user_tokens WHERE id = :id');
        $statement->execute([':id' => $id]);
    }
}