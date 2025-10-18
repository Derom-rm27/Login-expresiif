<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BannerRepository;

final class BannerController extends BaseController
{
    private BannerRepository $banners;

    public function __construct()
    {
        $this->banners = new BannerRepository();
    }

    public function moderatorDashboard(): void
    {
        $user = current_user();
        if (!$user || (!in_array('ROLE_MODERATOR', $user['roles'], true) && !in_array('ROLE_ADMIN', $user['roles'], true))) {
            redirect('/');
        }

        $this->render('pages/moderator_dashboard', [
            'title' => 'Gestión de banners',
            'banners' => $this->banners->all(),
        ]);
    }

    public function upload(): void
    {
        $user = current_user();
        if (!$user || (!in_array('ROLE_MODERATOR', $user['roles'], true) && !in_array('ROLE_ADMIN', $user['roles'], true))) {
            redirect('/login');
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!validate_csrf($token)) {
            flash('error', 'No pudimos validar tu solicitud.');
            redirect('/moderator-dashboard');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $filename = null;

        if (isset($_FILES['banner']) && is_uploaded_file($_FILES['banner']['tmp_name'])) {
            $extension = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('banner_', true) . '.' . strtolower($extension);
            move_uploaded_file($_FILES['banner']['tmp_name'], UPLOAD_PATH . '/' . $filename);
        }

        if ($title !== '') {
            $this->banners->create([
                'title' => $title,
                'description' => $description,
                'link' => $link !== '' ? $link : null,
                'image_path' => $filename,
                'is_active' => $isActive,
            ]);
            flash('success', 'El banner se guardó correctamente.');
        } else {
            flash('error', 'El título del banner es obligatorio.');
        }

        redirect('/moderator-dashboard');
    }

    public function toggle(int $id): void
    {
        $user = current_user();
        if (!$user || (!in_array('ROLE_MODERATOR', $user['roles'], true) && !in_array('ROLE_ADMIN', $user['roles'], true))) {
            redirect('/');
        }

        $this->banners->toggle($id);
        flash('success', 'El estado del banner se actualizó.');
        redirect('/moderator-dashboard');
    }

    public function delete(int $id): void
    {
        $user = current_user();
        if (!$user || (!in_array('ROLE_MODERATOR', $user['roles'], true) && !in_array('ROLE_ADMIN', $user['roles'], true))) {
            redirect('/');
        }

        $banner = $this->banners->find($id);
        if ($banner && $banner['image_path']) {
            $path = UPLOAD_PATH . '/' . $banner['image_path'];
            if (is_file($path)) {
                unlink($path);
            }
        }

        $this->banners->delete($id);
        flash('success', 'El banner se eliminó correctamente.');
        redirect('/moderator-dashboard');
    }
}