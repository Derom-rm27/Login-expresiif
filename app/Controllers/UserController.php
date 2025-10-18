<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\NewsRepository;
use App\Models\UserRepository;
use App\Models\UserTokenRepository;
use DateTimeImmutable;

final class UserController extends BaseController
{
    private UserRepository $users;
    private NewsRepository $news;
    private UserTokenRepository $tokens;

    public function __construct()
    {
        $this->users = new UserRepository();
        $this->news = new NewsRepository();
        $this->tokens = new UserTokenRepository();
    }

    public function profile(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        $this->render('pages/profile', [
            'title' => 'Mi perfil',
            'userProfile' => $this->users->findById((int) $user['id']) ?? $user,
        ]);
    }

    public function updateUsername(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No pudimos validar tu solicitud.');
            redirect('/profile');
        }

        $username = trim($_POST['username'] ?? '');
        if ($username !== '') {
            $this->users->updateUsername((int) $user['id'], $username);
            $updated = $this->users->findById((int) $user['id']);
            if ($updated) {
                refresh_current_user($updated);
            } else {
                $_SESSION['user']['username'] = $username;
            }
            flash('success', 'Actualizamos tu nombre de usuario.');
        } else {
            flash('error', 'El nombre de usuario no puede estar vacío.');
        }

        redirect('/profile');
    }

    public function updatePassword(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No pudimos validar tu solicitud.');
            redirect('/profile');
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $userRecord = $this->users->findById((int) $user['id']);
        if (!$userRecord) {
            flash('error', 'No pudimos encontrar tu cuenta.');
            redirect('/profile');
        }

        if (empty($userRecord['email_verified_at'])) {
            flash('error', 'Debes verificar tu correo antes de cambiar la contraseña.');
            redirect('/profile');
        }

        if (!password_verify($currentPassword, $userRecord['password'])) {
            flash('error', 'La contraseña actual no coincide.');
            redirect('/profile');
        }

        if ($password === '' || $password !== $confirm) {
            flash('error', 'Las contraseñas nuevas no coinciden.');
            redirect('/profile');
        }

        if (!password_is_strong($password)) {
            flash('error', 'La nueva contraseña no cumple con los requisitos de seguridad.');
            redirect('/profile');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $tokenValue = $this->tokens->create((int) $user['id'], 'password_change', ['password_hash' => $passwordHash], new DateTimeImmutable('+1 hour'));

        $link = app_url('/password/change/confirm/' . $tokenValue);
        $body = "Hola {$userRecord['username']},\n\nRecibimos una solicitud para actualizar tu contraseña. Para confirmar el cambio haz clic en el siguiente enlace:\n{$link}\n\nSi no solicitaste este cambio, ignora el mensaje y tu contraseña permanecerá igual.";

        send_system_mail($userRecord['email'], 'Confirma el cambio de contraseña', $body);

        flash('success', 'Te enviamos un correo para confirmar el cambio de contraseña. El enlace expira en una hora.');

        redirect('/profile');
    }

    public function userDashboard(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        $this->render('pages/user_dashboard', [
            'title' => 'Noticias recientes',
            'newsItems' => $this->news->latest(20),
        ]);
    }

    public function scrapeNews(): void
    {
        $user = current_user();
        if (!$user) {
            redirect('/login');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            redirect('/user-dashboard');
        }

        $samples = [
            [
                'title' => 'Calidad de software en 2025',
                'summary' => 'Las organizaciones apuestan por métricas continuas de calidad.',
                'author' => 'Equipo Editorial',
                'url' => 'https://example.com/calidad-2025',
                'image_url' => null,
                'source' => 'Tendencias',
            ],
            [
                'title' => 'Automatización de pruebas',
                'summary' => 'Nuevas herramientas aceleran la entrega de aplicaciones.',
                'author' => 'Equipo Editorial',
                'url' => 'https://example.com/automatizacion-pruebas',
                'image_url' => null,
                'source' => 'Automatización',
            ],
            [
                'title' => 'Equipos remotos y calidad',
                'summary' => 'Los equipos distribuidos adoptan prácticas colaborativas.',
                'author' => 'Equipo Editorial',
                'url' => 'https://example.com/equipos-remotos',
                'image_url' => null,
                'source' => 'Gestión',
            ],
        ];

        $this->news->replaceWith($samples);
        flash('success', 'Se actualizaron las noticias con la última simulación.');
        redirect('/user-dashboard');
    }

    public function adminDashboard(): void
    {
        $user = current_user();
        if (!$user || !in_array('ROLE_ADMIN', $user['roles'], true)) {
            redirect('/');
        }

        $this->render('pages/admin_dashboard', [
            'title' => 'Panel de administración',
            'users' => $this->users->all(),
        ]);
    }

    public function updateRoles(int $userId): void
    {
        $user = current_user();
        if (!$user || !in_array('ROLE_ADMIN', $user['roles'], true)) {
            redirect('/');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            redirect('/admin-dashboard');
        }

        $roles = $_POST['roles'] ?? [];
        if (!is_array($roles)) {
            $roles = [];
        }

        $normalized = array_values(array_unique(array_map(static function (string $role): string {
            $role = strtoupper($role);
            if (!str_starts_with($role, 'ROLE_')) {
                $role = 'ROLE_' . $role;
            }
            return $role;
        }, $roles)));

        if ($normalized === []) {
            $normalized = ['ROLE_USER'];
        }

        $this->users->updateRoles($userId, $normalized);

        if ((int) $user['id'] === $userId) {
            $updated = $this->users->findById($userId);
            if ($updated) {
                refresh_current_user($updated);
            } else {
                $_SESSION['user']['roles'] = $normalized;
            }
        }

        flash('success', 'Los roles del usuario se actualizaron correctamente.');
        redirect('/admin-dashboard');
    }
}