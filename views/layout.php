<?php use App\Core\Auth; use App\Core\CSRF; use App\Core\Router; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= e($title ?? 'Expedientes') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= Router::url('/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<?php if (!Auth::check()): include $viewFile; ?>
<?php else: ?>
<div class="d-flex">
  <aside class="sidebar p-3">
    <h5 class="mb-4">ExpedientesApp</h5>
    <a href="<?= Router::url('/dashboard') ?>">Inicio</a>
    <a href="<?= Router::url('/clients') ?>">Clientes</a>
    <a href="<?= Router::url('/cases') ?>">Expedientes</a>
    <?php if (Auth::hasRole('ADMIN')): ?>
      <a href="<?= Router::url('/users') ?>">Usuarios</a>
      <a href="<?= Router::url('/settings') ?>">Configuración</a>
      <a href="<?= Router::url('/logs') ?>">Bitácoras</a>
    <?php endif; ?>
    <form method="post" action="<?= Router::url('/logout') ?>" class="mt-4">
      <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
      <button class="btn btn-outline-light btn-sm w-100">Salir</button>
    </form>
  </aside>
  <main class="flex-fill p-3">
    <div class="bg-white rounded-3 p-3 mb-3 d-flex justify-content-between align-items-center ribbon">
      <div>
        <a class="btn btn-primary" href="#">Nuevo</a>
        <a class="btn btn-outline-secondary" href="#">Editar</a>
        <a class="btn btn-outline-secondary" href="#">Cerrar</a>
        <a class="btn btn-outline-secondary" href="<?= Router::url('/cases?export=csv') ?>">Exportar</a>
      </div>
      <div><strong><?= e(Auth::user()['name']) ?></strong></div>
    </div>
    <?php include $viewFile; ?>
  </main>
</div>
<?php endif; ?>
<script src="<?= Router::url('/assets/js/app.js') ?>"></script>
</body>
</html>
