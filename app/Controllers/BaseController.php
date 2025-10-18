<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BannerRepository;
use App\Models\NewsRepository;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        $data['currentUser'] = current_user();
        $data['activeBanners'] = $data['activeBanners'] ?? (new BannerRepository())->getActive();
        $data['activeNews'] = $data['activeNews'] ?? (new NewsRepository())->latest();
        view($view, $data);
    }
}