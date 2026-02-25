<?php

namespace App\Controllers;

use App\Core\DB;
use App\Core\View;
use App\Models\AuditLog;

class LogController
{
    public function index(): void
    {
        $notifications = DB::connection()->query('SELECT n.*, u.name user_name, c.folio FROM notification_log n LEFT JOIN users u ON u.id=n.user_id LEFT JOIN cases c ON c.id=n.case_id ORDER BY n.id DESC LIMIT 200')->fetchAll();
        View::render('pages/logs', [
            'title' => 'Bitácoras',
            'audit' => AuditLog::latest(),
            'notifications' => $notifications,
        ]);
    }
}
