<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Core/helpers.php';
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) return;
    $relative = str_replace('\\', '/', substr($class, 4));
    $file = dirname(__DIR__) . '/app/' . $relative . '.php';
    if (file_exists($file)) require_once $file;
});

App\Core\Env::load();

$mode = 'alerts';
if (PHP_SAPI === 'cli') {
    foreach ($argv as $arg) {
        if (str_starts_with($arg, '--mode=')) {
            $mode = substr($arg, 7);
        }
    }
} else {
    $token = $_GET['token'] ?? '';
    if (!hash_equals((string)env('CRON_TOKEN', 'change_me_cron_token'), $token)) {
        http_response_code(403);
        die('Token inválido');
    }
    $mode = $_GET['mode'] ?? 'alerts';
}

$settings = App\Models\Setting::get();
$warning = (int)$settings['warning_days'];
$cooldown = (int)$settings['cooldown_hours'];
$cases = App\Models\CaseFile::allWithRelations();
$notifier = new App\Services\NotificationService();

if ($mode === 'alerts') {
    foreach ($cases as $case) {
        $calc = App\Services\TrafficLightService::calculate($case, $warning);
        $newStatus = $calc['status'];
        if ($case['status'] !== $newStatus || $case['last_traffic_light'] !== $calc['traffic_light']) {
            App\Models\CaseFile::update((int)$case['id'], [
                'client_id' => $case['client_id'],
                'asunto_tipo' => $case['asunto_tipo'],
                'descripcion' => $case['descripcion'],
                'responsable_user_id' => $case['responsable_user_id'],
                'fecha_inicio' => $case['fecha_inicio'],
                'fecha_vencimiento' => $case['fecha_vencimiento'],
                'fecha_termino_real' => $case['fecha_termino_real'],
                'porcentaje_avance' => $case['porcentaje_avance'],
                'notas' => $case['notas'],
                'status' => $newStatus,
                'last_traffic_light' => $calc['traffic_light'],
            ]);

            $user = App\Models\User::find((int)$case['responsable_user_id']);
            if (!$user) continue;

            if ($calc['traffic_light'] === 'yellow' && $case['last_traffic_light'] !== 'yellow') {
                $notifier->notifyCase($case, $user, 'ALERTA_AMARILLA', $cooldown);
            }
            if ($calc['traffic_light'] === 'red' && $case['last_traffic_light'] !== 'red') {
                $notifier->notifyCase($case, $user, 'ALERTA_ROJA', $cooldown);
            }
        }
    }
    echo "OK alerts\n";
    exit;
}

if ($mode === 'digest') {
    $lawyers = App\Models\User::lawyers();
    foreach ($lawyers as $lawyer) {
        $mine = array_values(array_filter($cases, fn($c) => (int)$c['responsable_user_id'] === (int)$lawyer['id']));
        $bucket = ['green'=>[],'yellow'=>[],'red'=>[],'gray'=>[]];
        foreach ($mine as $case) {
            $light = App\Services\TrafficLightService::calculate($case, $warning)['traffic_light'];
            $bucket[$light][] = $case;
        }
        $user = App\Models\User::find((int)$lawyer['id']);
        if ($user) {
            $notifier->sendDigest($user, $bucket);
        }
    }
    echo "OK digest\n";
}
