<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
$tagPag = static function (string $s): string {
    return match ($s) {
        'capturado'  => 'green',
        'autorizado' => 'gold',
        'pendente'   => 'blue',
        'estornado', 'falhou' => 'red',
        default      => 'gray',
    };
};
?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Pagamentos & repasses</h2>
      <p>
        Gateway: <strong><?= esc($mpDriver ?? 'mock') ?></strong>
        <?php if (! empty($mpOk)): ?>
          · Mercado Pago <span class="tag green">configurado</span>
        <?php else: ?>
          · <span class="tag gold">mock / token ausente</span> — use .env para ativar MP
        <?php endif; ?>
      </p>
    </div>
  </div>

  <div class="stats">
    <div class="stat" style="--accent:var(--imbel-navy);--accent-bg:#e8eef5">
      <div class="num"><?= $fmt($totais['retido_aguardando_servico'] ?? 0) ?></div>
      <div class="lbl">Retido (serviço em andamento)</div>
    </div>
    <div class="stat" style="--accent:var(--imbel-emerald);--accent-bg:#ecfdf5">
      <div class="num"><?= $fmt($totais['comissao'] ?? 0) ?></div>
      <div class="lbl">Comissão retida (capturado)</div>
    </div>
    <div class="stat" style="--accent:var(--amber);--accent-bg:var(--amber-bg)">
      <div class="num"><?= $fmt($totais['a_repassar'] ?? 0) ?></div>
      <div class="lbl">A repassar prestadores</div>
    </div>
    <div class="stat" style="--accent:var(--teal);--accent-bg:var(--teal-bg)">
      <div class="num"><?= $fmt($totais['repassado'] ?? 0) ?></div>
      <div class="lbl">Já repassado</div>
    </div>
  </div>

  <?php if (! empty($aRepassar)): ?>
  <div class="panel" style="margin-bottom:16px">
    <div class="panel-head"><h3>Fila de repasses</h3></div>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>Pag.</th>
            <th>Agend.</th>
            <th>Prestador</th>
            <th>E-mail MP</th>
            <th>Líquido</th>
            <th>Comissão</th>
            <th>Ação</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($aRepassar as $p): ?>
            <tr>
              <td>#<?= (int) $p['id'] ?></td>
              <td><a href="<?= base_url('agendamentos/' . $p['agendamento_id']) ?>">#<?= (int) $p['agendamento_id'] ?></a></td>
              <td><?= esc($p['prestador_nome']) ?></td>
              <td><?= esc($p['mp_email'] ?: '—') ?></td>
              <td class="money"><?= $fmt($p['valor_liquido_prestador']) ?></td>
              <td class="money"><?= $fmt($p['valor_comissao']) ?></td>
              <td>
                <form method="post" action="<?= base_url('admin/pagamentos/' . $p['id'] . '/repassar') ?>" class="btn-row" style="gap:6px">
                  <?= csrf_field() ?>
                  <input class="input" name="ref" placeholder="Ref. transferência" style="min-width:120px;max-width:160px">
                  <input class="input" name="nota" placeholder="Nota (opcional)" style="min-width:120px">
                  <button class="btn btn-green" type="submit" onclick="return confirm('Confirma que o líquido já foi transferido ao prestador?');">Marcar repassado</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="panel-body hint">
      O valor bruto já está na conta Mercado Pago da plataforma. A comissão permanece retida; registre aqui o repasse do líquido ao prestador (PIX/transferência).
    </div>
  </div>
  <?php endif; ?>

  <div class="panel">
    <div class="panel-head"><h3>Todos os pagamentos</h3></div>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Agend.</th>
            <th>Cliente</th>
            <th>Prestador</th>
            <th>Bruto</th>
            <th>Comissão</th>
            <th>Líquido</th>
            <th>MP / Gateway</th>
            <th>Status</th>
            <th>Repasse</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lista as $p): ?>
            <tr>
              <td><?= (int) $p['id'] ?></td>
              <td><a href="<?= base_url('agendamentos/' . $p['agendamento_id']) ?>">#<?= (int) $p['agendamento_id'] ?></a></td>
              <td><?= esc($p['cliente_nome']) ?></td>
              <td><?= esc($p['prestador_nome']) ?></td>
              <td class="money"><?= $fmt($p['valor_bruto']) ?></td>
              <td class="money"><?= $fmt($p['valor_comissao']) ?></td>
              <td class="money"><?= $fmt($p['valor_liquido_prestador']) ?></td>
              <td>
                <code><?= esc($p['gateway']) ?></code>
                <?php if (! empty($p['mp_payment_id'])): ?>
                  <br><small>pay <?= esc($p['mp_payment_id']) ?></small>
                <?php elseif (! empty($p['gateway_ref'])): ?>
                  <br><small><?= esc($p['gateway_ref']) ?></small>
                <?php endif; ?>
              </td>
              <td><span class="tag <?= $tagPag($p['status']) ?>"><?= esc($p['status']) ?></span>
                <?php if (! empty($p['mp_status'])): ?><br><small><?= esc($p['mp_status']) ?></small><?php endif; ?>
              </td>
              <td><span class="tag <?= ($p['payout_status'] ?? '') === 'pago' ? 'green' : (($p['payout_status'] ?? '') === 'pendente' ? 'gold' : 'gray') ?>"><?= esc($p['payout_status'] ?? '—') ?></span></td>
              <td>
                <?php if (($p['gateway'] ?? '') === 'mercadopago'): ?>
                  <form method="post" action="<?= base_url('admin/pagamentos/' . $p['id'] . '/sincronizar') ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost" type="submit" style="height:34px;padding:0 10px;font-size:12px">Sync</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($lista)): ?>
            <tr><td colspan="11" style="text-align:center;color:var(--ink-500)">Nenhum pagamento.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
