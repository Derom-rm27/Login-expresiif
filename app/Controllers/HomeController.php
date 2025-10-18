<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController extends BaseController
{
    public function __invoke(): void
    {
        $this->render('pages/home', [
            'title' => APP_NAME,
        ]);
    }
}