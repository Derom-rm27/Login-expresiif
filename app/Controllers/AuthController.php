<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\LoginAttemptRepository;
use App\Models\UserRepository;
use App\Models\UserTokenRepository;
use DateTimeImmutable;
use PDOException;

final class AuthController extends BaseController
{
    private UserRepository $users;
    private UserTokenRepository $tokens;
    private LoginAttemptRepository $attempts;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->tokens = new UserTokenRepository();
        $this->attempts = new LoginAttemptRepository();
    }

    public function showLogin(): void
    {
        if (current_user()) {
            redirect('/profile');
        }

        $this->render('auth/login', [
            'title' => 'Iniciar sesión',
            'error' => null,
            'captchaQuestion' => generate_login_captcha(),
            'resendEmail' => null,
        ]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $captcha = $_POST['captcha'] ?? '';
        $token = $_POST['csrf_token'] ?? '';

        if (!validate_csrf($token)) {
            $this->render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Token CSRF inválido.',
                'captchaQuestion' => generate_login_captcha(),
                'resendEmail' => null,
            ]);
            return;
        }

        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $normalizedEmail = strtolower($email);

        if ($this->attempts->tooManyAttempts($normalizedEmail, $ipAddress)) {
            $this->render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Se superó el número de intentos permitidos. Espera unos minutos antes de volver a intentarlo.',
                'captchaQuestion' => generate_login_captcha(),
                'resendEmail' => null,
            ]);
            return;
        }

        if (!validate_login_captcha($captcha)) {
            $this->attempts->record($normalizedEmail, $ipAddress, false);
            $this->render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'La verificación CAPTCHA es incorrecta.',
                'captchaQuestion' => generate_login_captcha(),
                'resendEmail' => null,
            ]);
            return;
        }

        $user = $this->users->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->attempts->record($normalizedEmail, $ipAddress, false);
            $this->render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Credenciales inválidas.',
                'captchaQuestion' => generate_login_captcha(),
                'resendEmail' => null,
            ]);
            return;
        }

        if (empty($user['email_verified_at'])) {
            $this->attempts->record($normalizedEmail, $ipAddress, false);
            $this->render('auth/login', [
                'title' => 'Iniciar sesión',
                'error' => 'Debes confirmar tu correo electrónico antes de ingresar. Revisa tu bandeja o solicita un nuevo enlace.',
                'captchaQuestion' => generate_login_captcha(),
                'resendEmail' => $user['email'],
            ]);
            return;
        }

        session_regenerate_id(true);
        refresh_current_user($user);

        $this->attempts->clear($normalizedEmail, $ipAddress);
        $this->attempts->record($normalizedEmail, $ipAddress, true);

        redirect('/profile');
    }

    public function showRegister(): void
    {
        if (current_user()) {
            redirect('/profile');
        }

        $this->render('auth/register', [
            'title' => 'Crear cuenta',
            'error' => null,
            'formData' => [],
        ]);
    }

    public function register(): void
    {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $token = $_POST['csrf_token'] ?? '';

        if (!validate_csrf($token)) {
            $this->render('auth/register', [
                'title' => 'Crear cuenta',
                'error' => 'Token CSRF inválido.',
                'formData' => compact('username', 'email'),
            ]);
            return;
        }

        if ($password === '' || $password !== $confirm) {
            $this->render('auth/register', [
                'title' => 'Crear cuenta',
                'error' => 'Las contraseñas no coinciden.',
                'formData' => compact('username', 'email'),
            ]);
            return;
        }

        if (!password_is_strong($password)) {
            $this->render('auth/register', [
                'title' => 'Crear cuenta',
                'error' => 'La contraseña debe tener al menos 10 caracteres, incluir mayúsculas, minúsculas, números y símbolos.',
                'formData' => compact('username', 'email'),
            ]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth/register', [
                'title' => 'Crear cuenta',
                'error' => 'El correo electrónico no es válido.',
                'formData' => compact('username', 'email'),
            ]);
            return;
        }

        $existing = $this->users->findByEmail($email);
        if ($existing) {
            $this->render('auth/register', [
                'title' => 'Crear cuenta',
                'error' => 'El correo electrónico ya está registrado.',
                'formData' => compact('username', 'email'),
            ]);
            return;
        }

        try {
            $user = $this->users->create([
                'username' => $username ?: $email,
                'email' => $email,
                'password' => $password,
                'roles' => ['ROLE_USER'],
            ]);
        } catch (PDOException $exception) {
            $this->render('auth/register', [
                'title' => 'Crear cuenta',
                'error' => 'No fue posible crear la cuenta. Verifica que el correo o usuario no estén en uso.',
                'formData' => compact('username', 'email'),
            ]);
            return;
        }

        $this->sendEmailVerification((int) $user['id'], $user['email'], $user['username']);

        flash('success', 'Tu cuenta fue creada. Te enviamos un correo para confirmar la dirección.');
        redirect('/login');
    }

    public function logout(): void
    {
        session_destroy();
        session_start();
        session_regenerate_id(true);
        redirect('/');
    }

    public function verifyEmail(string $token): void
    {
        $record = $this->tokens->consume($token, 'email_verification');

        if (!$record) {
            flash('error', 'El enlace de verificación no es válido o ha expirado.');
            redirect('/login');
            return;
        }

        $userId = (int) $record['user_id'];
        $this->users->markEmailVerified($userId);
        $user = $this->users->findById($userId);

        if ($user !== null && ($current = current_user()) && (int) $current['id'] === $userId) {
            refresh_current_user($user);
            flash('success', 'Tu correo fue verificado correctamente.');
            redirect('/profile');
            return;
        }

        flash('success', 'Tu correo fue verificado correctamente. Ahora puedes iniciar sesión.');
        redirect('/login');
    }

    public function resendVerification(): void
    {
        if (!is_post()) {
            redirect('/login');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No fue posible procesar la solicitud.');
            redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('info', 'Si la cuenta existe recibirás un nuevo correo de verificación.');
            redirect('/login');
            return;
        }

        $user = $this->users->findByEmail($email);

        if (!$user) {
            flash('info', 'Si la cuenta existe recibirás un nuevo correo de verificación.');
            redirect('/login');
            return;
        }

        if (!empty($user['email_verified_at'])) {
            flash('success', 'Este correo ya fue verificado. Puedes iniciar sesión.');
            redirect('/login');
            return;
        }

        $this->sendEmailVerification((int) $user['id'], $user['email'], $user['username']);

        flash('success', 'Enviamos un nuevo enlace de verificación. Revisa tu bandeja o correo no deseado.');
        redirect('/login');
    }

    public function confirmPasswordChange(string $token): void
    {
        $record = $this->tokens->consume($token, 'password_change');

        if (!$record) {
            flash('error', 'El enlace para actualizar tu contraseña no es válido o expiró.');
            redirect('/login');
            return;
        }

        $userId = (int) $record['user_id'];
        $payload = $record['payload'];

        if (!isset($payload['password_hash'])) {
            flash('error', 'No se pudo completar la actualización de contraseña.');
            redirect('/login');
            return;
        }

        $this->users->updatePasswordHash($userId, $payload['password_hash']);

        if (($current = current_user()) && (int) $current['id'] === $userId) {
            flash('success', 'Tu contraseña fue actualizada. Vuelve a iniciar sesión.');
            session_destroy();
            session_start();
            session_regenerate_id(true);
        } else {
            flash('success', 'Tu contraseña fue actualizada. Ya puedes iniciar sesión.');
        }

        redirect('/login');
    }

    private function sendEmailVerification(int $userId, string $email, string $username): void
    {
        $token = $this->tokens->create($userId, 'email_verification', [], new DateTimeImmutable('+24 hours'));
        $link = app_url('/email/verify/' . $token);

        $body = "Hola {$username},\n\nGracias por registrarte en " . APP_NAME . ". Haz clic en el siguiente enlace para confirmar tu correo electrónico:\n{$link}\n\nSi no creaste esta cuenta, ignora este mensaje.";

        send_system_mail($email, 'Confirma tu correo electrónico', $body);
    }
}