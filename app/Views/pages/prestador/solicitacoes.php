<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
$sl  = $statusLabels ?? [];
$sc  = $statusCores ?? [];
?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Solicitações pendentes</h2>
      <p>Aceite ou rejeite. Em caso de rejeição, a reserva do pagamento é estornada.</p>
    </div>
  </div>

  <div class="panel">
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Data</th>
            <th>Você recebe</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($agendamentos as $a): ?>
            <tr>
              <td><?= (int) $a['id'] ?></td>
              <td><?= esc($a['cliente_nome']) ?></td>
              <td><?= esc($tiposServico[$a['tipo_servico']]['label'] ?? $a['tipo_servico']) ?></td>
              <td><?= esc(date('d/m/Y', strtotime($a['data_servico']))) ?> <?= esc(substr((string) $a['hora_inicio'], 0, 5)) ?></td>
              <td class="money"><?= $fmt($a['valor_prestador']) ?></td>
              <td><span class="tag <?= esc($sc[$a['status']] ?? 'gray') ?>"><?= esc($sl[$a['status']] ?? $a['status']) ?></span></td>
              <td class="btn-row">
                <a class="btn btn-ghost" href="<?= base_url('agendamentos/' . $a['id']) ?>">Abrir</a>
                <form method="post" action="<?= base_url('prestador/agendamentos/' . $a['id'] . '/aceitar') ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-green" type="submit">Aceitar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($agendamentos)): ?>
            <tr><td colspan="7" style="text-align:center;color:var(--ink-500)">Nenhuma solicitação pendente.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
