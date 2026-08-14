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
      <h2>Agendamentos</h2>
      <p>Acompanhe o ciclo completo do serviço e do pagamento</p>
    </div>
    <?php if (in_array($usuarioRole, ['cliente', 'admin'], true)): ?>
      <a class="btn btn-green" href="<?= base_url('agendamentos/novo') ?>"><svg class="ic"><use href="#i-plus"/></svg>Novo</a>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Tipo</th>
            <?php if ($usuarioRole !== 'cliente'): ?><th>Cliente</th><?php endif; ?>
            <?php if ($usuarioRole !== 'prestador'): ?><th>Prestador</th><?php endif; ?>
            <th>Data</th>
            <th>Valor</th>
            <th>Prestador recebe</th>
            <th>Comissão</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($agendamentos as $a): ?>
            <tr>
              <td><?= (int) $a['id'] ?></td>
              <td><?= esc($tiposServico[$a['tipo_servico']]['label'] ?? $a['tipo_servico']) ?></td>
              <?php if ($usuarioRole !== 'cliente'): ?><td><?= esc($a['cliente_nome'] ?? '—') ?></td><?php endif; ?>
              <?php if ($usuarioRole !== 'prestador'): ?><td><?= esc($a['prestador_nome'] ?? '—') ?></td><?php endif; ?>
              <td><?= esc(date('d/m/Y', strtotime($a['data_servico']))) ?> <?= esc(substr((string) $a['hora_inicio'], 0, 5)) ?></td>
              <td class="money"><?= $fmt($a['valor_total']) ?></td>
              <td class="money"><?= $fmt($a['valor_prestador']) ?></td>
              <td class="money"><?= $fmt($a['comissao_valor']) ?></td>
              <td><span class="tag <?= esc($sc[$a['status']] ?? 'gray') ?>"><?= esc($sl[$a['status']] ?? $a['status']) ?></span></td>
              <td class="btn-row">
                <a class="btn btn-ghost" href="<?= base_url('agendamentos/' . $a['id']) ?>">Detalhes</a>
                <?php if (in_array($usuarioRole, ['cliente', 'admin'], true) && ($a['status'] ?? '') === 'pendente'): ?>
                  <a class="btn btn-green" href="<?= base_url('pagamentos/checkout/' . $a['id']) ?>">Pagar</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($agendamentos)): ?>
            <tr><td colspan="10" style="text-align:center;color:var(--ink-500)">Nenhum agendamento.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
