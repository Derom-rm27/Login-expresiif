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

        $this->render('pages/visit_report', [
            'title' => 'Reporte de visitas',
            'visits' => $this->visits->all(),
        ]);
    }
}