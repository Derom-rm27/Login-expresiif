<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

final class VisitRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function increment(string $page): void
    {
        $statement = $this->db->prepare('INSERT INTO page_visits (page, visits) VALUES (:page, 1)
            ON DUPLICATE KEY UPDATE visits = visits + 1');
        $statement->execute([':page' => $page]);
    }

    /**
     * @return array<int, array{page:string, visits:int}>
     */
    public function all(): array
    {
        $statement = $this->db->query('SELECT page, visits FROM page_visits ORDER BY visits DESC');
        return $statement->fetchAll();
    }
}