<?php

declare(strict_types=1);

function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $templateFile = __DIR__ . '/../Views/' . $template . '.php';

    if (!is_file($templateFile)) {
        http_response_code(404);
        echo 'Vista no encontrada';
        return;
    }

    require __DIR__ . '/../Views/layouts/app.php';
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function current_user(): ?array
{
    if (!isset($_SESSION['user'])) {
        return null;
    }

    return $_SESSION['user'];
}

function refresh_current_user(array $user): void
{
    $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'],
        'roles' => $user['roles'],
        'email_verified_at' => $user['email_verified_at'] ?? null,
    ];
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function old(string $key, array $default = []): string
{
    return htmlspecialchars($default[$key] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validate_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function ensure_storage_paths(): void
{
    foreach ([STORAGE_PATH, STORAGE_PATH . '/mail', UPLOAD_PATH] as $path) {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }
}

function format_roles(array $roles): string
{
    return implode(', ', array_map(static fn (string $role): string => ucfirst(strtolower(str_replace('ROLE_', '', $role))), $roles));
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message === null) {
        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $value;
    }

    $_SESSION['flash'][$key] = $message;

    return null;
}

function app_url(string $path): string
{
    $path = '/' . ltrim($path, '/');

    if (BASE_URL === '') {
        return $path;
    }

    return BASE_URL . $path;
}

function send_system_mail(string $to, string $subject, string $body): void
{
    ensure_storage_paths();
    $directory = STORAGE_PATH . '/mail';

    $filename = sprintf(
        '%s/%s_%s.log',
        $directory,
        date('Ymd_His'),
        preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($to))
    );

    $content = sprintf(
        "From: %s <%s>\nTo: %s\nSubject: %s\nSent: %s\n\n%s",
        MAIL_FROM_NAME,
        MAIL_FROM_ADDRESS,
        $to,
        $subject,
        date('Y-m-d H:i:s'),
        $body
    );

    file_put_contents($filename, $content);
}

function generate_login_captcha(): string
{
    $a = random_int(1, 9);
    $b = random_int(1, 9);
    $_SESSION['login_captcha_answer'] = (string) ($a + $b);

    return sprintf('¿Cuánto es %d + %d?', $a, $b);
}

function validate_login_captcha(string $answer): bool
{
    if (!isset($_SESSION['login_captcha_answer'])) {
        return false;
    }

    $expected = $_SESSION['login_captcha_answer'];
    $isValid = hash_equals($expected, trim($answer));

    if ($isValid) {
        unset($_SESSION['login_captcha_answer']);
    }

    return $isValid;
}

function password_is_strong(string $password): bool
{
    if (strlen($password) < 10) {
        return false;
    }

    return preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[^a-zA-Z0-9]/', $password);
}