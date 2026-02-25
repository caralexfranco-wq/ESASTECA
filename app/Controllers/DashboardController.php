<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\CaseFile;
use App\Models\Setting;
use App\Services\TrafficLightService;

class DashboardController
{
    public function index(): void
    {
        $counts = CaseFile::dashboardCounts();
        $settings = Setting::get();
        $cases = CaseFile::allWithRelations();
        $soon = [];
        foreach ($cases as $case) {
            $calc = TrafficLightService::calculate($case, (int)$settings['warning_days']);
            if (in_array($calc['traffic_light'], ['yellow', 'red'], true)) {
                $case['traffic_light'] = $calc['traffic_light'];
                $case['days_remaining'] = $calc['days_remaining'];
                $soon[] = $case;
            }
        }

        View::render('pages/dashboard', [
            'title' => 'Dashboard',
            'counts' => $counts,
            'soon' => array_slice($soon, 0, 10),
            'myCases' => CaseFile::byResponsible((int)Auth::user()['id']),
        ]);
    }
}
