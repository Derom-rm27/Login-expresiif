<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

final class NewsRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<int, array>
     */
    public function latest(int $limit = 9): array
    {
        $statement = $this->db->prepare('SELECT * FROM news ORDER BY created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function replaceWith(array $newsItems): void
    {
        $this->db->beginTransaction();
        $this->db->exec('DELETE FROM news');

        $statement = $this->db->prepare('INSERT INTO news (title, summary, author, url, image_url, source, created_at)
            VALUES (:title, :summary, :author, :url, :image_url, :source, :created_at)');

        foreach ($newsItems as $item) {
            $statement->execute([
                ':title' => $item['title'],
                ':summary' => $item['summary'] ?? null,
                ':author' => $item['author'] ?? 'Desconocido',
                ':url' => $item['url'] ?? null,
                ':image_url' => $item['image_url'] ?? null,
                ':source' => $item['source'] ?? 'General',
                ':created_at' => date('Y-m-d H:i:s'),
        ]);
        }

        $this->db->commit();
    }
}