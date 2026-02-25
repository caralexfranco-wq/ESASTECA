<?php

declare(strict_types=1);

session_name('expedientes_session');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

date_default_timezone_set('America/Mexico_City');

require_once dirname(__DIR__) . '/app/Core/helpers.php';
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
}

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = dirname(__DIR__) . '/app/' . $relative . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

App\Core\Env::load();

$router = new App\Core\Router();

$router->get('/', [App\Controllers\DashboardController::class, 'index'], ['ADMIN','CAPTURISTA','CONSULTOR']);
$router->get('/login', [App\Controllers\AuthController::class, 'loginForm']);
$router->post('/login', [App\Controllers\AuthController::class, 'login']);
$router->post('/logout', [App\Controllers\AuthController::class, 'logout'], ['ADMIN','CAPTURISTA','CONSULTOR']);
$router->get('/dashboard', [App\Controllers\DashboardController::class, 'index'], ['ADMIN','CAPTURISTA','CONSULTOR']);

$router->get('/clients', [App\Controllers\ClientController::class, 'index'], ['ADMIN','CAPTURISTA','CONSULTOR']);
$router->post('/clients/store', [App\Controllers\ClientController::class, 'store'], ['ADMIN']);
$router->post('/clients/update', [App\Controllers\ClientController::class, 'update'], ['ADMIN']);

$router->get('/users', [App\Controllers\UserController::class, 'index'], ['ADMIN']);
$router->post('/users/store', [App\Controllers\UserController::class, 'store'], ['ADMIN']);
$router->post('/users/update', [App\Controllers\UserController::class, 'update'], ['ADMIN']);

$router->get('/cases', [App\Controllers\CaseController::class, 'index'], ['ADMIN','CAPTURISTA','CONSULTOR']);
$router->get('/cases/create', [App\Controllers\CaseController::class, 'createForm'], ['ADMIN','CAPTURISTA']);
$router->get('/cases/edit', [App\Controllers\CaseController::class, 'editForm'], ['ADMIN','CAPTURISTA']);
$router->post('/cases/store', [App\Controllers\CaseController::class, 'store'], ['ADMIN','CAPTURISTA']);
$router->post('/cases/update', [App\Controllers\CaseController::class, 'update'], ['ADMIN','CAPTURISTA']);

$router->get('/settings', [App\Controllers\SettingController::class, 'index'], ['ADMIN']);
$router->post('/settings/update', [App\Controllers\SettingController::class, 'update'], ['ADMIN']);

$router->get('/logs', [App\Controllers\LogController::class, 'index'], ['ADMIN']);

try {
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
} catch (Throwable $e) {
    App\Core\Logger::error($e->getMessage() . '\n' . $e->getTraceAsString());
    http_response_code(500);
    echo 'Error interno. Revisa storage/logs/app.log';
}
