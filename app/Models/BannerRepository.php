<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use PDO;

final class BannerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    /**
     * @return array<int, array>
     */
    public function all(): array
    {
        $statement = $this->db->query('SELECT * FROM banners ORDER BY created_at DESC');
        return $statement->fetchAll();
    }

    /**
     * @return array<int, array>
     */
    public function getActive(): array
    {
        $statement = $this->db->query('SELECT * FROM banners WHERE is_active = 1 ORDER BY created_at DESC');
        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM banners WHERE id = :id');
        $statement->execute([':id' => $id]);
        $banner = $statement->fetch();

        return $banner ?: null;
    }

    public function create(array $data): void
    {
        $statement = $this->db->prepare('INSERT INTO banners (title, description, image_path, link, is_active, created_at)
            VALUES (:title, :description, :image_path, :link, :is_active, :created_at)');

        $statement->execute([
            ':title' => $data['title'],
            ':description' => $data['description'] ?? null,
            ':image_path' => $data['image_path'] ?? null,
            ':link' => $data['link'] ?? null,
            ':is_active' => $data['is_active'] ?? 1,
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function update(int $id, array $data): void
    {
        $banner = $this->find($id);
        if (!$banner) {
            return;
        }

        $statement = $this->db->prepare('UPDATE banners SET title = :title, description = :description, image_path = :image_path, link = :link, is_active = :is_active WHERE id = :id');
        $statement->execute([
            ':title' => $data['title'] ?? $banner['title'],
            ':description' => $data['description'] ?? $banner['description'],
            ':image_path' => $data['image_path'] ?? $banner['image_path'],
            ':link' => $data['link'] ?? $banner['link'],
            ':is_active' => $data['is_active'] ?? $banner['is_active'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('DELETE FROM banners WHERE id = :id');
        $statement->execute([':id' => $id]);
    }

    public function toggle(int $id): void
    {
        $banner = $this->find($id);
        if (!$banner) {
            return;
        }

        $newState = $banner['is_active'] ? 0 : 1;
        $statement = $this->db->prepare('UPDATE banners SET is_active = :is_active WHERE id = :id');
        $statement->execute([':is_active' => $newState, ':id' => $id]);
    }
}