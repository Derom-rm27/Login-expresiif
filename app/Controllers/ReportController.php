<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\VisitRepository;

final class ReportController extends BaseController
{
    private VisitRepository $visits;

    public function __construct()
    {
        $this->visits = new VisitRepository();
    }

public function visits(): void
{
    $user = current_user();
    if (!$user || !in_array('ROLE_ADMIN', $user['roles'], true)) {
        redirect('/');
    }

    $totalVisits = $this->visits->totalVisits();
    
    $this->render('pages/visit_report', [
        'title' => 'Reporte de Visitas - Admin',
        'totalVisits' => $totalVisits,
        'todayVisits' => $this->visits->todayVisits(),
        'uniqueVisitors' => $this->visits->uniqueVisitors(),
        'statsByBrowser' => $this->visits->statsByBrowser(),
        'statsByOS' => $this->visits->statsByOS(),
        'visitsByIP' => $this->visits->visitsByIP(10),
        'visitsByHour' => $this->visits->visitsByHour(),
        'topPages' => $this->visits->top(10),
        'hasDetailedData' => $totalVisits > 0
    ]);
}
}