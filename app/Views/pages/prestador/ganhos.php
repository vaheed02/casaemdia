<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.'); ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Ganhos</h2>
      <p>Valores liberados após confirmação do cliente (captura no gateway)</p>
    </div>
  </div>

  <div class="stats">
    <div class="stat" style="--accent:var(--imbel-emerald);--accent-bg:#ecfdf5">
      <div class="num"><?= $fmt($resumo['total_recebido'] ?? 0) ?></div>
      <div class="lbl">Total líquido recebido</div>
    </div>
    <div class="stat" style="--accent:var(--imbel-gold);--accent-bg:#fbf1dc">
      <div class="num"><?= $fmt($resumo['total_comissao'] ?? 0) ?></div>
      <div class="lbl">Comissão retida pelo app</div>
    </div>
    <div class="stat" style="--accent:var(--imbel-navy);--accent-bg:#e8eef5">
      <div class="num"><?= (int) ($resumo['qtd'] ?? 0) ?></div>
      <div class="lbl">Pagamentos capturados</div>
    </div>
  </div>

  <div class="panel">
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>Agendamento</th>
            <th>Cliente</th>
            <th>Tipo</th>
            <th>Data</th>
            <th>Bruto</th>
            <th>Comissão</th>
            <th>Líquido</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $p): ?>
            <tr>
              <td><a href="<?= base_url('agendamentos/' . $p['agendamento_id']) ?>">#<?= (int) $p['agendamento_id'] ?></a></td>
              <td><?= esc($p['cliente_nome']) ?></td>
              <td><?= esc($tiposServico[$p['tipo_servico']]['label'] ?? $p['tipo_servico']) ?></td>
              <td><?= esc(date('d/m/Y', strtotime($p['data_servico']))) ?></td>
              <td class="money"><?= $fmt($p['valor_bruto']) ?></td>
              <td class="money"><?= $fmt($p['valor_comissao']) ?></td>
              <td class="money"><?= $fmt($p['valor_liquido_prestador']) ?></td>
              <td><span class="tag <?= $p['status'] === 'capturado' ? 'green' : 'gold' ?>"><?= esc($p['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($lista)): ?>
            <tr><td colspan="8" style="text-align:center;color:var(--ink-500)">Nenhum pagamento ainda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
