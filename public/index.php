<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\BannerController;
use App\Controllers\HomeController;
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

$router->get('/', [$homeController, '__invoke']);

$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/logout', [$authController, 'logout']);
$router->get('/email/verify/{token}', [$authController, 'verifyEmail']);
$router->post('/email/resend-verification', [$authController, 'resendVerification']);
$router->get('/password/change/confirm/{token}', [$authController, 'confirmPasswordChange']);

$router->get('/profile', [$userController, 'profile']);
$router->post('/profile/update-username', [$userController, 'updateUsername']);
$router->post('/profile/update-password', [$userController, 'updatePassword']);

$router->get('/user-dashboard', [$userController, 'userDashboard']);
$router->post('/scrape-news', [$userController, 'scrapeNews']);

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