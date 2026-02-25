<?php use App\Core\CSRF; use App\Core\Router; ?>
<div class="container py-5" style="max-width:420px;">
  <div class="card shadow-sm">
    <div class="card-body p-4">
      <h4>Acceso al sistema</h4>
      <?php if (!empty($_SESSION['flash_error'])): ?><div class="alert alert-danger"><?=$_SESSION['flash_error']; unset($_SESSION['flash_error']);?></div><?php endif; ?>
      <form method="post" action="<?= Router::url('/login') ?>">
        <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
        <div class="mb-2"><label>Email</label><input class="form-control" name="email" required></div>
        <div class="mb-3"><label>Contraseña</label><input class="form-control" type="password" name="password" required></div>
        <button class="btn btn-primary w-100">Ingresar</button>
      </form>
      <small class="text-muted d-block mt-3">Demo: admin@local / Password123!</small>
    </div>
  </div>
</div>
