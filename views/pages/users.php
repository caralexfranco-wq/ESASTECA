<?php use App\Core\CSRF; ?>
<div class="table-wrap">
  <h4>Usuarios</h4>
  <form method="post" action="<?= App\Core\Router::url('/users/store') ?>" class="row g-2 mb-3">
    <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
    <div class="col-md-2"><select class="form-select" name="role_id"><?php foreach($roles as $r): ?><option value="<?=$r['id']?>"><?=$r['key']?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><input class="form-control" name="name" placeholder="Nombre" required></div>
    <div class="col-md-2"><input class="form-control" name="email" placeholder="Email" required></div>
    <div class="col-md-2"><input class="form-control" name="phone" placeholder="Teléfono"></div>
    <div class="col-md-2"><input type="password" class="form-control" name="password" placeholder="Contraseña" required></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">Alta</button></div>
    <input type="hidden" name="is_active" value="1">
  </form>
  <table class="table table-striped"><thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th></tr></thead><tbody>
  <?php foreach ($users as $u): ?><tr><td><?=$u['id']?></td><td><?=e($u['name'])?></td><td><?=e($u['email'])?></td><td><?=e($u['role_key'])?></td><td><?=$u['is_active']?'Sí':'No'?></td></tr><?php endforeach; ?>
  </tbody></table>
</div>
