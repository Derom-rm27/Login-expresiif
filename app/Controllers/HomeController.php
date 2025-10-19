<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BannerRepository;
use App\Models\NewsRepository;
use App\Models\UserRepository;
use App\Models\VisitRepository;

final class HomeController extends BaseController
{
    public function __invoke(): void
    {
        $newsRepository = new NewsRepository();
        $bannerRepository = new BannerRepository();
        $userRepository = new UserRepository();
        $visitRepository = new VisitRepository();

        $latestNews = $newsRepository->latest(6);
        $tvPeruHighlights = $newsRepository->latestBySource('TVPerú', 3);

        $this->render('pages/home', [
            'title' => APP_NAME,
            'latestNews' => $latestNews,
            'tvPeruHighlights' => $tvPeruHighlights,
            'sourceStats' => $newsRepository->sourceStats(5),
            'metrics' => [
                'news' => $newsRepository->count(),
                'tvperu' => $newsRepository->countBySource('TVPerú'),
                'users' => $userRepository->count(),
                'banners' => $bannerRepository->countActive(),
                'visits' => $visitRepository->total(),
            ],
            'topPages' => $visitRepository->top(5),
            'activeBanners' => $bannerRepository->getActive(),
            'activeNews' => $latestNews,
        ]);
    }
}