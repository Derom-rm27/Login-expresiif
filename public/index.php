<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\BannerController;
use App\Controllers\HomeController;
use App\Controllers\NewsController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Models\VisitRepository;
use App\Router;

$router = new Router();
$authController = new AuthController();
$userController = new UserController();
$bannerController = new BannerController();
$reportController = new ReportController();
$homeController = new HomeController();
$newsController = new NewsController();


$visitTracker = new class {
    private \App\Models\VisitRepository $visits;

    public function __construct()
    {
        $this->visits = new \App\Models\VisitRepository();
    }

    public function track(): void
    {
        // SOLO contar la página principal (/) y SOLO si es GET
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($path === '/' || $path === '')) {
            try {
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
                $parsed = \App\Support\UserAgentParser::parse($userAgent);
                
                // Obtener IP real del cliente
                $clientIP = $this->getClientIP();
                
                // Registrar visita detallada
                $this->visits->recordDetailedVisit([
                    'ip_address' => $clientIP,
                    'user_agent' => $userAgent,
                    'browser' => $parsed['browser'],
                    'operating_system' => $parsed['os'],
                    'page_url' => '/'
                ]);
                
                // También mantener el contador simple (opcional)
                $this->visits->increment('/');
                
            } catch (\Exception $e) {
                // Silently fail
                error_log('Visit tracking error: ' . $e->getMessage());
            }
        }
    }

    private function getClientIP(): string
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? 
              $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_FORWARDED'] ?? 
              $_SERVER['HTTP_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_FORWARDED'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 
              '127.0.0.1';
        
        // Si hay múltiples IPs, tomar la primera
        if (strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }
        
        return $ip;
    }
};

$visitTracker->track();

$router->get('/', [$homeController, '__invoke']);

$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/logout', [$authController, 'logout']);
$router->get('/email/verify/{token}', [$authController, 'verifyEmail']);
$router->post('/email/resend-verification', [$authController, 'resendVerification']);
$router->get('/password/change/confirm/{token}', [$authController, 'confirmPasswordChange']);
$router->get('/captcha/image', [$authController, 'captchaImage']);

$router->get('/profile', [$userController, 'profile']);
$router->post('/profile/update-username', [$userController, 'updateUsername']);
$router->post('/profile/update-password', [$userController, 'updatePassword']);

$router->get('/user-dashboard', [$newsController, 'manage']);
// Agrega esta ruta
$router->post('/news/scrape', [App\Controllers\NewsController::class, 'scrapeAjax']);
$router->post('/news', [$newsController, 'store']);
$router->post('/news/{newsId}/update', [$newsController, 'update']);
$router->post('/news/{newsId}/delete', [$newsController, 'destroy']);

$router->get('/admin-dashboard', [$userController, 'adminDashboard']);
$router->post('/manage-users/update-roles/{userId}', [$userController, 'updateRoles']);

$router->get('/moderator-dashboard', [$bannerController, 'moderatorDashboard']);
$router->post('/upload-banner', [$bannerController, 'upload']);
$router->get('/toggle-banner/{id}', [$bannerController, 'toggle']);
$router->get('/delete-banner/{id}', [$bannerController, 'delete']);

$router->get('/visit-report', [$reportController, 'visits']);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    (new VisitRepository())->increment($uri);
}

$router->dispatch($method, rtrim($uri, '/') ?: '/');