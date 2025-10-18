<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

final class LoginAttemptRepository
{
    private const WINDOW_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function tooManyAttempts(string $email, string $ip): bool
    {
        $threshold = date('Y-m-d H:i:s', time() - (self::WINDOW_MINUTES * 60));

        $byIdentity = $this->countAttempts($email, $ip, $threshold);
        $byIp = $this->countAttempts(null, $ip, $threshold);

        return $byIdentity >= self::MAX_ATTEMPTS || $byIp >= self::MAX_ATTEMPTS;
    }

    public function record(string $email, string $ip, bool $successful): void
    {
        $statement = $this->db->prepare('INSERT INTO login_attempts (email, ip_address, successful, created_at)
            VALUES (:email, :ip, :successful, :created_at)');

        $statement->execute([
            ':email' => $email,
            ':ip' => $ip,
            ':successful' => $successful ? 1 : 0,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function clear(string $email, string $ip): void
    {
        $statement = $this->db->prepare('DELETE FROM login_attempts WHERE email = :email AND ip_address = :ip');
        $statement->execute([
            ':email' => $email,
            ':ip' => $ip,
        ]);
    }

    private function countAttempts(?string $email, string $ip, string $threshold): int
    {
        if ($email === null) {
            $statement = $this->db->prepare('SELECT COUNT(*) FROM login_attempts
                WHERE ip_address = :ip AND successful = 0 AND created_at >= :threshold');

            $statement->execute([
                ':ip' => $ip,
                ':threshold' => $threshold,
            ]);
        } else {
            $statement = $this->db->prepare('SELECT COUNT(*) FROM login_attempts
                WHERE email = :email AND ip_address = :ip AND successful = 0 AND created_at >= :threshold');

            $statement->execute([
                ':email' => $email,
                ':ip' => $ip,
                ':threshold' => $threshold,
            ]);
        }

        return (int) $statement->fetchColumn();
    }
}