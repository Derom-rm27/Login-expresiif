<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NewsRepository;
use App\Support\TvPeruScraper;
use RuntimeException;
use Exception;
final class NewsController extends BaseController
{
    private NewsRepository $news;

    public function __construct()
    {
        $this->news = new NewsRepository();
    }

    public function manage(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        if (!$this->canPublish($user)) {
            flash('error', 'Tu cuenta no tiene permisos para gestionar noticias.');
            redirect('/');
        }

        $this->render('pages/user_dashboard', [
            'title' => 'Mis noticias',
            'userNews' => $this->news->byCreator((int) $user['id']),
            'latestNews' => $this->news->latest(12),
            'tvPeruNews' => $this->news->latestBySource('TVPerú', 3),
            'canPublish' => true,
        ]);
    }

    public function store(): void
    {
        $user = $this->requirePublisher();

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No pudimos validar tu solicitud.');
            redirect('/user-dashboard');
        }

        $scrapeUrl = trim($_POST['scrape_url'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $author = trim($_POST['author'] ?? $user['username']);
        $source = trim($_POST['source'] ?? 'Comunidad');
        $url = trim($_POST['url'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

        if ($scrapeUrl !== '') {
            try {
                $scraped = (new TvPeruScraper())->scrape($scrapeUrl);
                $title = $title !== '' ? $title : $scraped['title'];
                $summary = $summary !== '' ? $summary : ($scraped['summary'] ?? '');
                $author = $author !== '' ? $author : ($scraped['author'] ?? $user['username']);
                $imageUrl = $imageUrl !== '' ? $imageUrl : ($scraped['image_url'] ?? '');
                $url = $scraped['url'];
                $source = 'TVPerú';
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/user-dashboard');
            }
        }

        if ($title === '' || $summary === '') {
            flash('error', 'La noticia debe incluir al menos un título y un resumen.');
            redirect('/user-dashboard');
        }

        $this->news->create([
            'title' => $title,
            'summary' => $summary,
            'author' => $author !== '' ? $author : $user['username'],
            'url' => $url !== '' ? $url : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'source' => $source !== '' ? $source : 'Comunidad',
            'created_by' => (int) $user['id'],
        ]);

        flash('success', 'Tu noticia fue publicada correctamente.');
        redirect('/user-dashboard');
    }

    public function update(int $newsId): void
    {
        $user = $this->requirePublisher();

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No pudimos validar tu solicitud.');
            redirect('/user-dashboard');
        }

        $news = $this->news->find($newsId);
        if (!$news) {
            flash('error', 'No encontramos la noticia seleccionada.');
            redirect('/user-dashboard');
        }

        if (!$this->canEdit($user, $news)) {
            flash('error', 'No tienes permisos para editar esta noticia.');
            redirect('/user-dashboard');
        }

        $scrapeUrl = trim($_POST['scrape_url'] ?? '');
        $title = trim($_POST['title'] ?? $news['title']);
        $summary = trim($_POST['summary'] ?? $news['summary'] ?? '');
        $author = trim($_POST['author'] ?? ($news['author'] ?? $user['username']));
        $source = trim($_POST['source'] ?? ($news['source'] ?? 'Comunidad'));
        $url = trim($_POST['url'] ?? ($news['url'] ?? ''));
        $imageUrl = trim($_POST['image_url'] ?? ($news['image_url'] ?? ''));

        if ($scrapeUrl !== '') {
            try {
                $scraped = (new TvPeruScraper())->scrape($scrapeUrl);
                $title = $scraped['title'];
                $summary = $scraped['summary'] ?? $summary;
                $author = $scraped['author'] ?? $author;
                $imageUrl = $scraped['image_url'] ?? $imageUrl;
                $url = $scraped['url'];
                $source = 'TVPerú';
            } catch (RuntimeException $exception) {
                flash('error', $exception->getMessage());
                redirect('/user-dashboard');
            }
        }

        if ($title === '' || $summary === '') {
            flash('error', 'Debes indicar un título y un resumen para la noticia.');
            redirect('/user-dashboard');
        }

        $this->news->update($newsId, [
            'title' => $title,
            'summary' => $summary,
            'author' => $author !== '' ? $author : $news['author'],
            'url' => $url !== '' ? $url : null,
            'image_url' => $imageUrl !== '' ? $imageUrl : null,
            'source' => $source !== '' ? $source : $news['source'],
        ]);

        flash('success', 'La noticia se actualizó correctamente.');
        redirect('/user-dashboard');
    }

    public function destroy(int $newsId): void
    {
        $user = $this->requirePublisher();

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No pudimos validar tu solicitud.');
            redirect('/user-dashboard');
        }

        $news = $this->news->find($newsId);
        if (!$news) {
            flash('error', 'No encontramos la noticia que intentas eliminar.');
            redirect('/user-dashboard');
        }

        if (!$this->canEdit($user, $news)) {
            flash('error', 'No tienes permisos para eliminar esta noticia.');
            redirect('/user-dashboard');
        }

        $this->news->delete($newsId);
        flash('success', 'La noticia se eliminó correctamente.');
        redirect('/user-dashboard');
    }

    private function requirePublisher(): array
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        if (!$this->canPublish($user)) {
            flash('error', 'No cuentas con permisos para gestionar noticias.');
            redirect('/');
        }

        return $user;
    }

    private function canPublish(array $user): bool
    {
        return in_array('ROLE_ADMIN', $user['roles'], true) || in_array('ROLE_USER', $user['roles'], true);
    }

    private function canEdit(array $user, array $news): bool
    {
        if (in_array('ROLE_ADMIN', $user['roles'], true)) {
            return true;
        }

        return isset($news['created_by']) && (int) $news['created_by'] === (int) $user['id'];
    }

    public function scrapeAjax(): void
{
    // Configurar headers para JSON
    header('Content-Type: application/json');
    
    try {
        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            echo json_encode([
                'success' => false,
                'error' => 'Token de seguridad inválido'
            ]);
            return;
        }

        $url = trim($_POST['url'] ?? '');
        
        if (empty($url)) {
            echo json_encode([
                'success' => false,
                'error' => 'No se proporcionó una URL'
            ]);
            return;
        }

        // Usar el scraper
        $scraped = (new TvPeruScraper())->scrape($url);
        
        echo json_encode([
            'success' => true,
            'news' => $scraped
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
}

