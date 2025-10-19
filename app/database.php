<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            ensure_storage_paths();

            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_DATABASE, DB_CHARSET);

            try {
                self::$connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $exception) {
                http_response_code(500);
                exit('Error al conectar con la base de datos: ' . $exception->getMessage());
            }

            self::migrate();
            self::seed();
        }

        return self::$connection;
    }

    public static function initialize(): void
    {
        self::connection();
    }

    private static function migrate(): void
    {
        $db = self::$connection;

        $db->exec('CREATE TABLE IF NOT EXISTS users (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(190) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            roles JSON NOT NULL,
            email_verified_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET);

        $db->exec('CREATE TABLE IF NOT EXISTS user_tokens (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NOT NULL,
            token CHAR(64) NOT NULL UNIQUE,
            type VARCHAR(32) NOT NULL,
            payload JSON NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_user_tokens_user_type (user_id, type),
            CONSTRAINT fk_user_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET);

        $db->exec('CREATE TABLE IF NOT EXISTS banners (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT NULL,
            image_path VARCHAR(255) NULL,
            link VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET);

        $db->exec('CREATE TABLE IF NOT EXISTS news (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            summary TEXT NULL,
            author VARCHAR(255) NULL,
            url VARCHAR(255) NULL,
            image_url VARCHAR(255) NULL,
            source VARCHAR(100) NULL,
            created_by INT UNSIGNED NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            CONSTRAINT fk_news_users FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET);

        self::ensureColumn($db, 'news', 'created_by', 'ALTER TABLE news ADD COLUMN created_by INT UNSIGNED NULL AFTER source, ADD CONSTRAINT fk_news_users FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL');
        self::ensureColumn($db, 'news', 'updated_at', 'ALTER TABLE news ADD COLUMN updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER created_at');

        $db->exec('CREATE TABLE IF NOT EXISTS page_visits (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            page VARCHAR(255) NOT NULL UNIQUE,
            visits INT UNSIGNED NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET);

        $db->exec('CREATE TABLE IF NOT EXISTS login_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(190) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            successful TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            INDEX idx_login_attempts_email_ip (email, ip_address),
            INDEX idx_login_attempts_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . DB_CHARSET);
    }

    private static function seed(): void
    {
        $db = self::$connection;

        $exists = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($exists === 0) {
            $statement = $db->prepare('INSERT INTO users (username, email, password, roles, email_verified_at, created_at, updated_at) VALUES (:username, :email, :password, :roles, :email_verified_at, :created_at, :updated_at)');
            $now = date('Y-m-d H:i:s');
            $statement->execute([
                ':username' => 'admin',
                ':email' => 'admin@example.com',
                ':password' => password_hash('Admin123!', PASSWORD_DEFAULT),
                ':roles' => json_encode(['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_USER'], JSON_THROW_ON_ERROR),
                ':email_verified_at' => $now,
                ':created_at' => $now,
                ':updated_at' => $now,
            ]);
        }

        $bannerExists = (int) $db->query('SELECT COUNT(*) FROM banners')->fetchColumn();
        if ($bannerExists === 0) {
            $statement = $db->prepare('INSERT INTO banners (title, description, image_path, link, is_active, created_at) VALUES (:title, :description, :image_path, :link, :is_active, :created_at)');
            $statement->execute([
                ':title' => 'Bienvenido a la plataforma',
                ':description' => 'Explora las últimas noticias y gestiona tu perfil.',
                ':image_path' => null,
                ':link' => null,
                ':is_active' => 1,
                ':created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $newsExists = (int) $db->query('SELECT COUNT(*) FROM news')->fetchColumn();
        if ($newsExists === 0) {
            $samples = [
                ['IA revoluciona la educación', 'La inteligencia artificial está transformando el aprendizaje personalizado.', 'Equipo Editorial', 'https://example.com/ia-educacion', null, 'Tecnología'],
                ['Nuevas prácticas ágiles', 'Las empresas adoptan marcos ágiles para mejorar la calidad del software.', 'Equipo Editorial', 'https://example.com/practicas-agiles', null, 'Agilidad'],
                ['Tendencias en ciberseguridad', 'Los ataques dirigidos impulsan nuevas estrategias de protección.', 'Equipo Editorial', 'https://example.com/ciberseguridad', null, 'Seguridad'],
            ];

            $newsStatement = $db->prepare('INSERT INTO news (title, summary, author, url, image_url, source, created_by, created_at, updated_at)
                VALUES (:title, :summary, :author, :url, :image_url, :source, :created_by, :created_at, :updated_at)');
            $now = date('Y-m-d H:i:s');

            foreach ($samples as $sample) {
                $newsStatement->execute([
                    ':title' => $sample[0],
                    ':summary' => $sample[1],
                    ':author' => $sample[2],
                    ':url' => $sample[3],
                    ':image_url' => $sample[4],
                    ':source' => $sample[5],
                    ':created_by' => 1,
                    ':created_at' => $now,
                    ':updated_at' => $now,
                ]);
            }
        }
    }

    private static function ensureColumn(PDO $db, string $table, string $column, string $statement): void
    {
        $query = $db->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $query->execute([
            ':table' => $table,
            ':column' => $column,
        ]);

        if ((int) $query->fetchColumn() === 0) {
            $db->exec($statement);
        }
    }
}