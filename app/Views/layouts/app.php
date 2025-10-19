<?php
/** @var array|null $currentUser */
/** @var array $activeBanners */
/** @var array $activeNews */
/** @var string $templateFile */
/** @var string|null $title */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? APP_NAME, ENT_QUOTES) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <?php if (captcha_mode() === 'turnstile'): ?>
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/"><?= APP_NAME ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/">Inicio</a></li>
                <?php if ($currentUser): ?>
                    <li class="nav-item"><a class="nav-link" href="/profile">Mi Perfil</a></li>
                    <?php if (in_array('ROLE_ADMIN', $currentUser['roles'], true)): ?>
                        <li class="nav-item"><a class="nav-link" href="/admin-dashboard">Admin</a></li>
                        <li class="nav-item"><a class="nav-link" href="/moderator-dashboard">Banners</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user-dashboard">Noticias</a></li>
                    <?php elseif (in_array('ROLE_MODERATOR', $currentUser['roles'], true)): ?>
                        <li class="nav-item"><a class="nav-link" href="/moderator-dashboard">Banners</a></li>
                        <?php if (in_array('ROLE_USER', $currentUser['roles'], true)): ?>
                            <li class="nav-item"><a class="nav-link" href="/user-dashboard">Noticias</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/user-dashboard">Noticias</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="/visit-report">Visitas</a></li>
                    <li class="nav-item"><a class="nav-link" href="/logout">Cerrar Sesión</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login">Iniciar Sesión</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Registrarse</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php if ($successMessage = flash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($successMessage, ENT_QUOTES) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage = flash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        <?php if ($infoMessage = flash('info')): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($infoMessage, ENT_QUOTES) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        <?php require $templateFile; ?>
    </div>
</main>

<footer class="bg-dark text-white py-3 mt-auto">
    <div class="container text-center">
        <small>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Todos los derechos reservados.</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>