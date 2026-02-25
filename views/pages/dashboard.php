<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>Abiertos</h6><h3><?= (int)($counts['abiertos'] ?? 0) ?></h3></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>En riesgo</h6><h3><?= (int)($counts['riesgo'] ?? 0) ?></h3></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>Vencidos</h6><h3><?= (int)($counts['vencidos'] ?? 0) ?></h3></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><h6>Cerrados</h6><h3><?= (int)($counts['cerrados'] ?? 0) ?></h3></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-7 table-wrap">
    <h5>Vencen pronto</h5>
    <table class="table table-sm"><thead><tr><th>Folio</th><th>Cliente</th><th>Semáforo</th><th>Días</th></tr></thead><tbody>
    <?php foreach ($soon as $c): ?><tr><td><?=e($c['folio'])?></td><td><?=e($c['client_name'])?></td><td><span class="badge status-<?=$c['traffic_light']?>"><?=e($c['traffic_light'])?></span></td><td><?=e((string)$c['days_remaining'])?></td></tr><?php endforeach; ?>
    </tbody></table>
  </div>
  <div class="col-lg-5 table-wrap">
    <h5>Mis expedientes</h5>
    <ul class="list-group list-group-flush">
      <?php foreach ($myCases as $m): ?><li class="list-group-item"><?=e($m['folio'])?> - <?=e($m['descripcion'])?></li><?php endforeach; ?>
    </ul>
  </div>
</div>
