<?php

declare(strict_types=1);

namespace App\Models;

use App\Database;
use App\Support\UserAgentParser;
use PDO;

final class VisitRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    // Método existente (mantener para compatibilidad)
    public function increment(string $page): void
    {
        $statement = $this->db->prepare('INSERT INTO page_visits (page, visits) VALUES (:page, 1)
            ON DUPLICATE KEY UPDATE visits = visits + 1');
        $statement->execute([':page' => $page]);
    }

    // Nuevo método para tracking detallado
    public function recordDetailedVisit(array $data): void
    {
        $statement = $this->db->prepare('
            INSERT INTO detailed_visits (ip_address, user_agent, browser, operating_system, page_url, visit_time)
            VALUES (:ip_address, :user_agent, :browser, :operating_system, :page_url, NOW())
        ');
        
        $statement->execute([
            ':ip_address' => $data['ip_address'],
            ':user_agent' => $data['user_agent'],
            ':browser' => $data['browser'],
            ':operating_system' => $data['operating_system'],
            ':page_url' => $data['page_url']
        ]);
    }

    // Métodos para estadísticas
    public function totalVisits(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM detailed_visits')->fetchColumn();
    }

    public function todayVisits(): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM detailed_visits WHERE visit_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)'
        );
        $statement->execute();
        return (int) $statement->fetchColumn();
    }

    public function uniqueVisitors(): int
    {
        return (int) $this->db->query('SELECT COUNT(DISTINCT ip_address) FROM detailed_visits')->fetchColumn();
    }

    /**
     * @return array<int, array{browser:string, count:int, percentage:float}>
     */
    public function statsByBrowser(): array
    {
        $total = $this->totalVisits();
        $statement = $this->db->query('
            SELECT 
                browser,
                COUNT(*) as count
            FROM detailed_visits 
            GROUP BY browser 
            ORDER BY count DESC
        ');
        
        $browsers = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function($browser) use ($total) {
            $browser['percentage'] = $total > 0 ? round(($browser['count'] / $total) * 100, 1) : 0;
            return $browser;
        }, $browsers);
    }

    /**
     * @return array<int, array{operating_system:string, count:int, percentage:float}>
     */
    public function statsByOS(): array
    {
        $total = $this->totalVisits();
        $statement = $this->db->query('
            SELECT 
                operating_system,
                COUNT(*) as count
            FROM detailed_visits 
            GROUP BY operating_system 
            ORDER BY count DESC
        ');
        
        $os = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function($os) use ($total) {
            $os['percentage'] = $total > 0 ? round(($os['count'] / $total) * 100, 1) : 0;
            return $os;
        }, $os);
    }

    /**
     * @return array<int, array{ip_address:string, visit_count:int, last_visit:string}>
     */
    public function visitsByIP(int $limit = 10): array
    {
        $statement = $this->db->prepare('
            SELECT 
                ip_address,
                COUNT(*) as visit_count,
                MAX(visit_time) as last_visit
            FROM detailed_visits 
            GROUP BY ip_address 
            ORDER BY visit_count DESC 
            LIMIT :limit
        ');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * @return array<int, array{hour:int, visits:int}>
     */
    public function visitsByHour(): array
    {
        $statement = $this->db->query('
            SELECT 
                HOUR(visit_time) as hour,
                COUNT(*) as visits
            FROM detailed_visits 
            GROUP BY HOUR(visit_time)
            ORDER BY hour
        ');
        
        return $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // Métodos legacy (mantener compatibilidad)
    public function all(): array
    {
        $statement = $this->db->query('SELECT page, visits FROM page_visits ORDER BY visits DESC');
        return $statement->fetchAll();
    }

    public function total(): int
    {
        $total = $this->db->query('SELECT SUM(visits) FROM page_visits')->fetchColumn();
        return $total !== null ? (int) $total : 0;
    }

    public function top(int $limit = 5): array
    {
        $statement = $this->db->prepare('SELECT page, visits FROM page_visits ORDER BY visits DESC LIMIT :limit');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}