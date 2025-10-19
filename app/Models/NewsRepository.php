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
        $statement = $this->db->prepare('SELECT n.*, u.username AS owner_username FROM news n
            LEFT JOIN users u ON n.created_by = u.id
            ORDER BY n.created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    /**
     * @return array<int, array>
     */
    public function byCreator(int $userId): array
    {
        $statement = $this->db->prepare('SELECT n.*, u.username AS owner_username FROM news n
            LEFT JOIN users u ON n.created_by = u.id
            WHERE n.created_by = :created_by
            ORDER BY n.created_at DESC');
        $statement->execute([':created_by' => $userId]);

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT n.*, u.username AS owner_username FROM news n
            LEFT JOIN users u ON n.created_by = u.id
            WHERE n.id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $news = $statement->fetch();

        return $news ? $this->hydrate($news) : null;
    }

    public function create(array $data): array
    {
        $statement = $this->db->prepare('INSERT INTO news (title, summary, author, url, image_url, source, created_by, created_at, updated_at)
            VALUES (:title, :summary, :author, :url, :image_url, :source, :created_by, :created_at, :updated_at)');

        $now = date('Y-m-d H:i:s');
        $statement->execute([
            ':title' => $data['title'],
            ':summary' => $data['summary'] ?? null,
            ':author' => $data['author'] ?? null,
            ':url' => $data['url'] ?? null,
            ':image_url' => $data['image_url'] ?? null,
            ':source' => $data['source'] ?? null,
            ':created_by' => $data['created_by'] ?? null,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);

        return $this->find((int) $this->db->lastInsertId()) ?? [];
    }

    public function update(int $id, array $data): void
    {
        $existing = $this->find($id);
        if ($existing === null) {
            return;
        }

        $statement = $this->db->prepare('UPDATE news SET title = :title, summary = :summary, author = :author,
            url = :url, image_url = :image_url, source = :source, updated_at = :updated_at WHERE id = :id');

        $statement->execute([
            ':title' => $data['title'] ?? $existing['title'],
            ':summary' => $data['summary'] ?? $existing['summary'],
            ':author' => $data['author'] ?? $existing['author'],
            ':url' => $data['url'] ?? $existing['url'],
            ':image_url' => $data['image_url'] ?? $existing['image_url'],
            ':source' => $data['source'] ?? $existing['source'],
            ':updated_at' => date('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM news WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM news')->fetchColumn();
    }

    public function countBySource(string $source): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM news WHERE source = :source');
        $statement->execute([':source' => $source]);

        return (int) $statement->fetchColumn();
    }

    /**
     * @return array<int, array{source:string,total:int}>
     */
    public function sourceStats(int $limit = 5): array
    {
        $statement = $this->db->prepare("SELECT COALESCE(NULLIF(source, ''), 'Sin fuente') AS source, COUNT(*) AS total
            FROM news GROUP BY source ORDER BY total DESC LIMIT :limit");
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array>
     */
    public function latestBySource(string $source, int $limit = 3): array
    {
        $statement = $this->db->prepare('SELECT n.*, u.username AS owner_username FROM news n
            LEFT JOIN users u ON n.created_by = u.id
            WHERE n.source = :source
            ORDER BY n.created_at DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':source', $source);
        $statement->execute();

        return array_map([$this, 'hydrate'], $statement->fetchAll());
    }

    /**
     * @param array<string, mixed> $news
     * @return array<string, mixed>
     */
    private function hydrate(array $news): array
    {
        if (!isset($news['source']) || $news['source'] === null || $news['source'] === '') {
            $news['source'] = 'General';
        }

        return $news;
    }
}