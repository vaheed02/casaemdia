<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.'); ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Comissões da plataforma</h2>
      <p>Valores retidos automaticamente na conta Mercado Pago da plataforma após confirmação do serviço.</p>
    </div>
  </div>

  <div class="panel" style="margin-bottom:16px">
    <div class="panel-body">
      <form method="post" action="<?= base_url('admin/comissoes') ?>" class="grid-2" style="align-items:end">
        <?= csrf_field() ?>
        <div class="field" style="margin:0">
          <label>Comissão padrão sobre novos serviços (%)</label>
          <input class="input" type="number" name="comissao_percentual" min="0" max="90" step="0.01"
            value="<?= esc(number_format((float) $pct, 2, '.', '')) ?>" required>
          <p class="hint">Aplicada no momento do agendamento (não altera serviços já criados).</p>
        </div>
        <div>
          <button class="btn btn-green" type="submit">Salvar comissão</button>
        </div>
      </form>
    </div>
  </div>

  <div class="stats">
    <div class="stat" style="--accent:var(--imbel-emerald);--accent-bg:#ecfdf5">
      <div class="num"><?= $fmt($totais['comissao'] ?? 0) ?></div>
      <div class="lbl">Total de comissões capturadas</div>
    </div>
    <div class="stat" style="--accent:var(--imbel-navy);--accent-bg:#e8eef5">
      <div class="num"><?= (int) ($totais['qtd'] ?? 0) ?></div>
      <div class="lbl">Serviços faturados</div>
    </div>
    <div class="stat" style="--accent:var(--amber);--accent-bg:var(--amber-bg)">
      <div class="num"><?= number_format((float) $pct, 2, ',', '.') ?>%</div>
      <div class="lbl">Percentual atual</div>
    </div>
  </div>

  <div class="panel">
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>Agend.</th>
            <th>Cliente</th>
            <th>Prestador</th>
            <th>Bruto</th>
            <th>Comissão</th>
            <th>Capturado em</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $p): ?>
            <tr>
              <td><a href="<?= base_url('agendamentos/' . $p['agendamento_id']) ?>">#<?= (int) $p['agendamento_id'] ?></a></td>
              <td><?= esc($p['cliente_nome']) ?></td>
              <td><?= esc($p['prestador_nome']) ?></td>
              <td class="money"><?= $fmt($p['valor_bruto']) ?></td>
              <td class="money"><?= $fmt($p['valor_comissao']) ?></td>
              <td><?= $p['capturado_em'] ? esc(date('d/m/Y H:i', strtotime($p['capturado_em']))) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($lista)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--ink-500)">Nenhuma comissão capturada ainda.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
