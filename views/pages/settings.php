<?php use App\Core\CSRF; ?>
<div class="table-wrap" style="max-width:640px;">
  <h4>Configuración general</h4>
  <form method="post" action="<?= App\Core\Router::url('/settings/update') ?>" class="row g-3">
    <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
    <div class="col-md-4"><label>warning_days</label><input type="number" class="form-control" name="warning_days" value="<?=$settings['warning_days']?>"></div>
    <div class="col-md-4"><label>cooldown_hours</label><input type="number" class="form-control" name="cooldown_hours" value="<?=$settings['cooldown_hours']?>"></div>
    <div class="col-md-4"><label>digest_time</label><input type="time" class="form-control" name="digest_time" value="<?=$settings['digest_time']?>"></div>
    <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="email_enabled" <?=$settings['email_enabled']?'checked':''?>><label class="form-check-label">Email habilitado</label></div>
    <div class="col-12 form-check"><input class="form-check-input" type="checkbox" name="whatsapp_enabled" <?=$settings['whatsapp_enabled']?'checked':''?>><label class="form-check-label">WhatsApp habilitado</label></div>
    <div class="col-12"><button class="btn btn-primary">Guardar</button></div>
  </form>
</div>
