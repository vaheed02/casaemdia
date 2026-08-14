<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
$sl  = $statusLabels ?? [];
$sc  = $statusCores ?? [];
$st  = $ag['status'];
$role = $usuarioRole;
$uid  = (int) session('id');
$isCliente   = $role === 'cliente' || ($role === 'admin' && (int) $ag['cliente_id'] === $uid);
$isPrestador = $role === 'prestador' || ($role === 'admin' && (int) $ag['prestador_id'] === $uid);
// Admin vê ações de ambos se for o caso; simplificar:
$isCliente = in_array($role, ['cliente', 'admin'], true) && ((int) $ag['cliente_id'] === $uid || $role === 'admin');
$isPrestador = in_array($role, ['prestador', 'admin'], true) && ((int) $ag['prestador_id'] === $uid || $role === 'admin');

$flow = ['pendente','aceito','em_andamento','aguardando_confirmacao','confirmado','pago'];
$idx  = array_search($st, $flow, true);
?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Agendamento #<?= (int) $ag['id'] ?></h2>
      <p><?= esc($tiposServico[$ag['tipo_servico']]['label'] ?? $ag['tipo_servico']) ?> ·
        <span class="tag <?= esc($sc[$st] ?? 'gray') ?>"><?= esc($sl[$st] ?? $st) ?></span>
      </p>
    </div>
    <a class="btn btn-ghost" href="<?= base_url('agendamentos') ?>">Voltar</a>
  </div>

  <div class="flow-steps">
    <?php
      $labels = [
        'pendente' => 'Agendado',
        'aceito' => 'Aceito',
        'em_andamento' => 'Em execução',
        'aguardando_confirmacao' => 'Concluído',
        'confirmado' => 'Confirmado',
        'pago' => 'Pago',
      ];
      foreach ($labels as $k => $lab):
        $i = array_search($k, $flow, true);
        $cls = 'step';
        if ($st === 'rejeitado' || $st === 'cancelado') {
          $cls .= $k === 'pendente' ? ' on' : '';
        } else {
          if ($idx !== false && $i !== false && $i < $idx) $cls .= ' done';
          if ($k === $st || ($st === 'pago' && $k === 'pago')) $cls .= ' on';
          if ($st === 'pago' && $k !== 'pago' && $i !== false) $cls .= ' done';
        }
    ?>
      <span class="<?= $cls ?>"><?= esc($lab) ?></span>
    <?php endforeach; ?>
  </div>

  <div class="grid-2">
    <div class="panel">
      <div class="panel-head"><h3>Detalhes</h3></div>
      <div class="panel-body">
        <div class="field"><label>Cliente</label><div><?= esc($ag['cliente_nome']) ?> · <?= esc($ag['cliente_telefone'] ?: $ag['cliente_email']) ?></div></div>
        <div class="field"><label>Prestador</label><div><?= esc($ag['prestador_nome']) ?> · <?= esc($ag['prestador_telefone'] ?: $ag['prestador_email']) ?></div></div>
        <div class="field"><label>Quando</label><div><?= esc(date('d/m/Y', strtotime($ag['data_servico']))) ?> às <?= esc(substr((string) $ag['hora_inicio'], 0, 5)) ?> · <?= esc($ag['duracao_horas']) ?>h</div></div>
        <div class="field"><label>Local</label><div>
          <?php if (! empty($ag['logradouro'])): ?>
            <?= esc($ag['logradouro']) ?>, <?= esc($ag['numero']) ?> — <?= esc($ag['bairro']) ?>, <?= esc($ag['cidade']) ?>/<?= esc($ag['uf']) ?>
          <?php else: ?>—<?php endif; ?>
        </div></div>
        <?php if (! empty($ag['observacoes_cliente'])): ?>
          <div class="field"><label>Observações</label><div><?= esc($ag['observacoes_cliente']) ?></div></div>
        <?php endif; ?>
        <?php if (! empty($ag['motivo_rejeicao'])): ?>
          <div class="field"><label>Motivo da rejeição</label><div><?= esc($ag['motivo_rejeicao']) ?></div></div>
        <?php endif; ?>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Valores e pagamento</h3></div>
      <div class="panel-body">
        <div class="field"><label>Valor total</label><div class="money"><?= $fmt($ag['valor_total']) ?></div></div>
        <div class="field"><label>Comissão app (<?= esc($ag['comissao_percentual']) ?>%)</label><div class="money"><?= $fmt($ag['comissao_valor']) ?></div></div>
        <div class="field"><label>Prestador recebe</label><div class="money" style="color:var(--imbel-emerald)"><?= $fmt($ag['valor_prestador']) ?></div></div>
        <?php if ($pagamento): ?>
          <div class="field"><label>Gateway</label><div><?= esc($pagamento['gateway']) ?>
            <?php if (! empty($pagamento['mp_payment_id'])): ?> · pay <?= esc($pagamento['mp_payment_id']) ?>
            <?php elseif (! empty($pagamento['gateway_ref'])): ?> · <?= esc($pagamento['gateway_ref']) ?>
            <?php endif; ?>
          </div></div>
          <div class="field"><label>Status pagamento</label>
            <div><span class="tag <?= $pagamento['status'] === 'capturado' ? 'green' : ($pagamento['status'] === 'autorizado' ? 'gold' : ($pagamento['status'] === 'pendente' ? 'blue' : 'gray')) ?>"><?= esc($pagamento['status']) ?></span>
              <?php if (! empty($pagamento['mp_status'])): ?><small> · MP <?= esc($pagamento['mp_status']) ?></small><?php endif; ?>
            </div>
          </div>
          <?php if (! empty($pagamento['payout_status']) && $pagamento['payout_status'] !== 'nao_aplicavel'): ?>
            <div class="field"><label>Repasse prestador</label>
              <div><span class="tag <?= $pagamento['payout_status'] === 'pago' ? 'green' : 'gold' ?>"><?= esc($pagamento['payout_status']) ?></span></div>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if ($isCliente && $pagamento && in_array($pagamento['status'], ['pendente', 'falhou'], true)): ?>
  <div class="panel" style="margin-top:14px;border-color:var(--imbel-emerald);background:var(--green-bg)">
    <div class="panel-body">
      <h3 style="margin-bottom:8px">Pagamento pendente</h3>
      <p style="margin-bottom:12px;font-size:14px;color:var(--ink-700)">
        Valor a pagar: <strong class="money"><?= $fmt($pagamento['valor_bruto'] ?? $ag['valor_total']) ?></strong>.
        Sem o pagamento, o prestador <strong>não pode aceitar</strong> o serviço.
        O valor fica retido na plataforma até você confirmar a execução.
      </p>
      <div class="btn-row">
        <a class="btn btn-green" href="<?= base_url('pagamentos/checkout/' . $ag['id']) ?>">
          <svg class="ic"><use href="#i-money"/></svg>
          <?= ($pagamento['gateway'] ?? '') === 'mercadopago' ? 'Pagar com Mercado Pago' : 'Pagar agora' ?>
        </a>
        <?php if (($pagamento['gateway'] ?? '') === 'mercadopago'): ?>
        <form method="post" action="<?= base_url('pagamentos/' . $ag['id'] . '/sincronizar') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-ghost" type="submit">Já paguei — atualizar status</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="panel" style="margin-top:14px">
    <div class="panel-head"><h3>Ações</h3></div>
    <div class="panel-body btn-row">

      <?php if ($isCliente && $pagamento && in_array($pagamento['status'], ['pendente', 'falhou'], true)): ?>
        <a class="btn btn-green" href="<?= base_url('pagamentos/checkout/' . $ag['id']) ?>">
          <svg class="ic"><use href="#i-money"/></svg>
          <?= ($pagamento['gateway'] ?? '') === 'mercadopago' ? 'Pagar com Mercado Pago' : 'Pagar agora' ?>
        </a>
      <?php endif; ?>

      <?php if ($isPrestador && $st === 'pendente'): ?>
        <?php if ($pagamento && ! in_array($pagamento['status'], ['autorizado', 'capturado'], true)): ?>
          <span class="hint">Aguardando pagamento do cliente para aceitar.</span>
        <?php else: ?>
        <form method="post" action="<?= base_url('prestador/agendamentos/' . $ag['id'] . '/aceitar') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-green" type="submit"><svg class="ic"><use href="#i-check"/></svg>Aceitar</button>
        </form>
        <form method="post" action="<?= base_url('prestador/agendamentos/' . $ag['id'] . '/rejeitar') ?>" style="display:flex;gap:8px;flex-wrap:wrap">
          <?= csrf_field() ?>
          <input class="input" type="text" name="motivo" placeholder="Motivo (opcional)" style="min-width:180px">
          <button class="btn btn-ghost" type="submit"><svg class="ic"><use href="#i-x"/></svg>Rejeitar</button>
        </form>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($isPrestador && $st === 'aceito'): ?>
        <form method="post" action="<?= base_url('prestador/agendamentos/' . $ag['id'] . '/iniciar') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-green" type="submit"><svg class="ic"><use href="#i-play"/></svg>Iniciar serviço</button>
        </form>
      <?php endif; ?>

      <?php if ($isPrestador && $st === 'em_andamento'): ?>
        <form method="post" action="<?= base_url('prestador/agendamentos/' . $ag['id'] . '/concluir') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-green" type="submit"><svg class="ic"><use href="#i-check"/></svg>Marcar como concluído</button>
        </form>
      <?php endif; ?>

      <?php if ($st === 'aguardando_confirmacao'): ?>
        <?php if ($isCliente): ?>
        <div class="alert alert-success" style="width:100%;margin-bottom:10px">
          O prestador marcou o serviço como concluído. Confirme abaixo para liberar a comissão da plataforma e colocar o líquido do prestador na fila de repasse do admin.
          <strong>Isso não depende de webhook</strong> — a confirmação grava tudo no sistema.
        </div>
        <form method="post" action="<?= base_url('agendamentos/' . $ag['id'] . '/confirmar') ?>" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;width:100%">
          <?= csrf_field() ?>
          <div class="field" style="margin:0">
            <label>Nota (1–5)</label>
            <select class="input" name="nota" style="min-width:100px">
              <option value="">Sem nota</option>
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <option value="<?= $i ?>"><?= $i ?> ★</option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="field" style="margin:0;flex:1;min-width:200px">
            <label>Comentário</label>
            <input class="input" type="text" name="comentario" placeholder="Como foi o serviço?">
          </div>
          <button class="btn btn-green" type="submit"><svg class="ic"><use href="#i-money"/></svg>Confirmar serviço e liberar repasse</button>
        </form>
        <?php elseif ($isPrestador): ?>
          <span class="hint">Serviço concluído. Aguardando o <strong>cliente confirmar</strong> (ou o admin liberar o repasse).</span>
        <?php elseif ($usuarioRole === 'admin' && $pagamento): ?>
          <form method="post" action="<?= base_url('admin/pagamentos/' . $pagamento['id'] . '/liberar') ?>" onsubmit="return confirm('Confirmar pelo cliente e liberar repasse?');">
            <?= csrf_field() ?>
            <button class="btn btn-green" type="submit">Admin: liberar repasse agora</button>
          </form>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (in_array($st, ['pendente', 'aceito'], true) && ($isCliente || $isPrestador)): ?>
        <form method="post" action="<?= base_url('agendamentos/' . $ag['id'] . '/cancelar') ?>" onsubmit="return confirm('Cancelar este agendamento?');">
          <?= csrf_field() ?>
          <button class="btn btn-ghost" type="submit">Cancelar agendamento</button>
        </form>
      <?php endif; ?>

      <?php if ($st === 'pago'): ?>
        <span class="tag green">Serviço pago — comissão app <?= $fmt($ag['comissao_valor']) ?> retida · líquido prestador <?= $fmt($ag['valor_prestador']) ?>
          <?php if ($pagamento && ($pagamento['payout_status'] ?? '') === 'pago'): ?> (repassado)<?php else: ?> (aguardando repasse admin)<?php endif; ?>
        </span>
      <?php endif; ?>

      <?php if (! in_array($st, ['pendente','aceito','em_andamento','aguardando_confirmacao','pago'], true)): ?>
        <span class="hint">Nenhuma ação disponível neste status.</span>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
