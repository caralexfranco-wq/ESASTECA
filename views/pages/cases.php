<?php use App\Core\Auth; ?>
<div class="table-wrap">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h4>Expedientes</h4>
    <?php if (Auth::hasRole(['ADMIN','CAPTURISTA'])): ?><a class="btn btn-primary" href="<?=App\Core\Router::url('/cases/create')?>">Alta</a><?php endif; ?>
  </div>
  <form class="row g-2 mb-3" method="get" action="<?=App\Core\Router::url('/cases')?>">
    <div class="col-md-3"><input class="form-control" name="q" value="<?=e($filters['q'])?>" placeholder="Buscar folio/descripcion"></div>
    <div class="col-md-3"><select class="form-select" name="client_id"><option value="">Cliente</option><?php foreach($clients as $c):?><option value="<?=$c['id']?>" <?=$filters['client_id']==$c['id']?'selected':''?>><?=e($c['razon_social'])?></option><?php endforeach;?></select></div>
    <div class="col-md-2"><select class="form-select" name="status"><option value="">Estado</option><?php foreach(['Abierto','En Riesgo','Vencido','Cerrado'] as $s):?><option <?=$filters['status']==$s?'selected':''?>><?=$s?></option><?php endforeach;?></select></div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
  </form>
  <table class="table table-hover table-sm"><thead><tr><th>Folio</th><th>Cliente</th><th>Asunto</th><th>Responsable</th><th>Vence</th><th>Semáforo</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
  <?php foreach($cases as $c): $light=$c['calc']['traffic_light']; ?>
    <tr>
      <td><?=e($c['folio'])?></td><td><?=e($c['client_name'])?></td><td><?=e($c['asunto_tipo'])?></td><td><?=e($c['responsable_name'])?></td><td><?=e($c['fecha_vencimiento'])?></td>
      <td><span class="badge status-<?=$light?>"><?=$light?></span> <?=$c['calc']['days_remaining']!==null?'('.$c['calc']['days_remaining'].'d)':''?></td>
      <td><?=e($c['status'])?></td>
      <td><?php if (Auth::hasRole(['ADMIN','CAPTURISTA'])):?><a class="btn btn-sm btn-outline-secondary" href="<?=App\Core\Router::url('/cases/edit?id='.$c['id'])?>">Editar</a><?php endif; ?></td>
    </tr>
  <?php endforeach;?>
  </tbody></table>
  <?php $pages=max(1,ceil($total/15)); ?><nav><ul class="pagination"><?php for($i=1;$i<=$pages;$i++): ?><li class="page-item <?=$i===$page?'active':''?>"><a class="page-link" href="?page=<?=$i?>"><?=$i?></a></li><?php endfor; ?></ul></nav>
</div>
