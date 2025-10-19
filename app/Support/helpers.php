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

function send_system_mail(string $to, string $subject, string $body): bool
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

    // SOLO usar mail() nativo por ahora
    return send_real_email_gmail($to, $subject, $body);
}

function send_real_email_gmail(string $to, string $subject, string $body): bool
{
    // Cargar PHPMailer manualmente
    $phpmailerPath = __DIR__ . '/../../vendor/PHPMailer/PHPMailer/src/';
    if (is_dir($phpmailerPath)) {
        require_once $phpmailerPath . 'Exception.php';
        require_once $phpmailerPath . 'PHPMailer.php';
        require_once $phpmailerPath . 'SMTP.php';
    }
    
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer not available");
        return false;
    }
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Configuración Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = ''; // Tu email
        $mail->Password = ''; // App Password de Gmail
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Destinatarios
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Contenido
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $body;
        
        $result = $mail->send();
        error_log("Gmail email sent successfully to: $to");
        return $result;
        
    } catch (Exception $e) {
        error_log("Gmail error: " . $e->getMessage());
        return false;
    }
}

function is_local_environment(): bool
{
    return ($_SERVER['HTTP_HOST'] ?? '') === 'localhost:8000' || 
           ($_SERVER['SERVER_NAME'] ?? '') === 'localhost';
}


function captcha_mode(): string
{
    return (TURNSTILE_SECRET !== '' && TURNSTILE_SITE_KEY !== '') ? 'turnstile' : 'image';
}

/**
 * @return array{mode:string, site_key?:string, image_url?:string, prompt?:string}
 */
function generate_login_captcha(): array
{
    if (captcha_mode() === 'turnstile') {
        return [
            'mode' => 'turnstile',
            'site_key' => TURNSTILE_SITE_KEY,
        ];
    }

    $code = (string) random_int(10000, 99999);
    $_SESSION['login_captcha_answer'] = $code;
    $_SESSION['login_captcha_generated_at'] = time();

    return [
        'mode' => 'image',
        'image_url' => '/captcha/image?ts=' . microtime(true),
        'prompt' => 'Escribe los números de la imagen',
    ];
}

function validate_login_captcha(?string $answer, ?string $turnstileToken, string $ipAddress): bool
{
    if (captcha_mode() === 'turnstile') {
        return $turnstileToken !== null && verify_turnstile_token($turnstileToken, $ipAddress);
    }

    if (!isset($_SESSION['login_captcha_answer'])) {
        return false;
    }

    $expected = $_SESSION['login_captcha_answer'];
    $isValid = hash_equals($expected, trim((string) $answer));

    if ($isValid) {
        unset($_SESSION['login_captcha_answer'], $_SESSION['login_captcha_generated_at']);
    }

    return $isValid;
}

function verify_turnstile_token(string $token, string $ipAddress): bool
{
    if (TURNSTILE_SECRET === '') {
        return false;
    }

    $payload = http_build_query([
        'secret' => TURNSTILE_SECRET,
        'response' => $token,
        'remoteip' => $ipAddress,
    ]);

    $endpoint = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $response = null;

    if (function_exists('curl_init')) {
        $curl = curl_init($endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($curl);
        curl_close($curl);
    }

    if ($response === null || $response === false) {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 5,
            ],
        ]);

        $response = @file_get_contents($endpoint, false, $context);
    }

    if ($response === false || $response === null) {
        return false;
    }

    $data = json_decode($response, true);
    return is_array($data) && !empty($data['success']);
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