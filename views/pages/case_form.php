<?php use App\Core\CSRF; $edit = !empty($case); ?>
<div class="table-wrap">
  <h4><?= $edit ? 'Editar expediente' : 'Alta expediente' ?></h4>
  <form method="post" action="<?= App\Core\Router::url($edit ? '/cases/update' : '/cases/store') ?>" class="row g-3">
    <input type="hidden" name="_csrf" value="<?= CSRF::token() ?>">
    <?php if($edit):?><input type="hidden" name="id" value="<?=$case['id']?>"><?php endif; ?>
    <div class="col-md-4"><label>Cliente</label><select class="form-select" name="client_id" required><?php foreach($clients as $c):?><option value="<?=$c['id']?>" <?=($case['client_id']??'')==$c['id']?'selected':''?>><?=e($c['razon_social'])?></option><?php endforeach;?></select></div>
    <div class="col-md-4"><label>Tipo asunto</label><select class="form-select" name="asunto_tipo"><option>SeguridadHigiene</option><option>Capacitacion</option><option>Otro</option></select></div>
    <div class="col-md-4"><label>Responsable</label><select class="form-select" name="responsable_user_id"><?php foreach($lawyers as $u):?><option value="<?=$u['id']?>" <?=($case['responsable_user_id']??'')==$u['id']?'selected':''?>><?=e($u['name'])?></option><?php endforeach;?></select></div>
    <div class="col-md-6"><label>Descripción corta</label><input class="form-control" name="descripcion" value="<?=e($case['descripcion'] ?? '')?>" required></div>
    <div class="col-md-3"><label>Fecha inicio</label><input type="date" class="form-control" name="fecha_inicio" value="<?=e($case['fecha_inicio'] ?? date('Y-m-d'))?>" required></div>
    <div class="col-md-3"><label>Fecha vencimiento</label><input type="date" class="form-control" name="fecha_vencimiento" value="<?=e($case['fecha_vencimiento'] ?? date('Y-m-d',strtotime('+15 days')))?>" required></div>
    <div class="col-md-3"><label>Fecha término real</label><input type="date" class="form-control" name="fecha_termino_real" value="<?=e($case['fecha_termino_real'] ?? '')?>"></div>
    <div class="col-md-3"><label>% avance</label><input type="number" min="0" max="100" class="form-control" name="porcentaje_avance" value="<?=e((string)($case['porcentaje_avance'] ?? 0))?>"></div>
    <div class="col-md-6"><label>Notas</label><textarea class="form-control" name="notas"><?=e($case['notas'] ?? '')?></textarea></div>
    <div class="col-12"><label>Comentario de historial</label><input class="form-control" name="comentario" value=""></div>
    <div class="col-12"><button class="btn btn-primary">Guardar</button></div>
  </form>

  <?php if($edit): ?>
  <h5 class="mt-4">Historial</h5>
  <table class="table table-sm"><thead><tr><th>Fecha</th><th>Actor</th><th>%</th><th>Comentario</th></tr></thead><tbody>
  <?php foreach($history as $h):?><tr><td><?=$h['created_at']?></td><td><?=e($h['actor_name'])?></td><td><?=$h['porcentaje_avance']?></td><td><?=e($h['comentario'])?></td></tr><?php endforeach;?>
  </tbody></table>
  <?php endif; ?>
</div>
