<?php

namespace App\Controllers;

use App\Core\CSRF;
use App\Core\View;
use App\Models\Setting;

class SettingController
{
    public function index(): void
    {
        View::render('pages/settings', ['title' => 'Configuración', 'settings' => Setting::get()]);
    }

    public function update(): void
    {
        CSRF::verify($_POST['_csrf'] ?? null);
        Setting::update([
            'warning_days' => (int)$_POST['warning_days'],
            'digest_time' => $_POST['digest_time'],
            'cooldown_hours' => (int)$_POST['cooldown_hours'],
            'whatsapp_enabled' => isset($_POST['whatsapp_enabled']) ? 1 : 0,
            'email_enabled' => isset($_POST['email_enabled']) ? 1 : 0,
        ]);
        redirect('/settings');
    }
}
