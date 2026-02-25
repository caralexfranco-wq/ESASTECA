<?php use App\Core\Auth; use App\Core\CSRF; ?>
<div class="table-wrap">
  <div class="d-flex justify-content-between"><h4>Clientes</h4></div>
  <?php if (Auth::hasRole('ADMIN')): ?>
  <form method="post" action="<?= App\Core\Router::url('/clients/store') ?>" class="row g-2 mb-3">
    <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
    <div class="col-md-2"><select class="form-select" name="type"><option>empresa</option><option>oficina</option><option>gasolinera</option></select></div>
    <div class="col-md-3"><input class="form-control" name="razon_social" placeholder="Razón social" required></div>
    <div class="col-md-2"><input class="form-control" name="rfc" placeholder="RFC"></div>
    <div class="col-md-2"><input class="form-control" name="contacto" placeholder="Contacto"></div>
    <div class="col-md-2"><input class="form-control" name="email" placeholder="Email"></div>
    <div class="col-md-1"><button class="btn btn-primary w-100">Alta</button></div>
    <div class="col-12"><input class="form-control" name="address" placeholder="Dirección"></div>
    <input type="hidden" name="phone" value="">
    <input type="hidden" name="is_active" value="1">
  </form>
  <?php endif; ?>
  <table class="table table-striped"><thead><tr><th>ID</th><th>Tipo</th><th>Razón Social</th><th>Email</th><th>Activo</th></tr></thead><tbody>
  <?php foreach ($clients as $c): ?><tr><td><?=$c['id']?></td><td><?=e($c['type'])?></td><td><?=e($c['razon_social'])?></td><td><?=e($c['email'])?></td><td><?=$c['is_active']?'Sí':'No'?></td></tr><?php endforeach; ?>
  </tbody></table>
</div>
