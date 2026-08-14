<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
$sl  = $statusLabels ?? [];
$sc  = $statusCores ?? [];
$role = $usuarioRole ?? '';
$c = $contagens ?? [];
?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Painel</h2>
      <p>
        <?php if ($role === 'cliente'): ?>
          Agende diarista, pets, telhado, piscinas, jardins ou hidráulica. O pagamento só é liberado após você confirmar o serviço.
        <?php elseif ($role === 'prestador'): ?>
          Aceite solicitações, execute o serviço e receba após a confirmação do cliente.
        <?php else: ?>
          Visão geral da plataforma, pagamentos e comissões.
        <?php endif; ?>
      </p>
    </div>
    <div class="btn-row">
      <?php if ($role === 'cliente' || $role === 'admin'): ?>
        <a class="btn btn-green" href="<?= base_url('prestadores') ?>"><svg class="ic"><use href="#i-search"/></svg>Buscar prestadores</a>
        <a class="btn btn-ghost" href="<?= base_url('agendamentos/novo') ?>"><svg class="ic"><use href="#i-plus"/></svg>Novo agendamento</a>
      <?php endif; ?>
      <?php if ($role === 'prestador'): ?>
        <a class="btn btn-green" href="<?= base_url('prestador/solicitacoes') ?>"><svg class="ic"><use href="#i-bell"/></svg>Solicitações</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="flow-steps">
    <span class="step done">1. Cliente agenda</span>
    <span class="step done">2. Prestador aceita</span>
    <span class="step done">3. Executa</span>
    <span class="step done">4. Cliente confirma</span>
    <span class="step on">5. Gateway libera · app comissão</span>
  </div>

  <div class="stats">
    <?php if ($role === 'cliente'): ?>
      <div class="stat" style="--accent:var(--imbel-gold);--accent-bg:#fbf1dc">
        <div class="num"><?= (int) ($c['pendente'] ?? 0) ?></div>
        <div class="lbl">Aguardando prestador</div>
      </div>
      <div class="stat" style="--accent:var(--teal);--accent-bg:var(--teal-bg)">
        <div class="num"><?= (int) (($c['aceito'] ?? 0) + ($c['em_andamento'] ?? 0)) ?></div>
        <div class="lbl">Em curso</div>
      </div>
      <div class="stat" style="--accent:var(--imbel-emerald);--accent-bg:#ecfdf5">
        <div class="num"><?= (int) ($c['aguardando_confirmacao'] ?? 0) ?></div>
        <div class="lbl">Para confirmar</div>
      </div>
      <div class="stat" style="--accent:var(--imbel-navy);--accent-bg:#e8eef5">
        <div class="num"><?= (int) ($c['pago'] ?? 0) ?></div>
        <div class="lbl">Concluídos / pagos</div>
      </div>
    <?php elseif ($role === 'prestador'): ?>
      <div class="stat" style="--accent:var(--imbel-gold);--accent-bg:#fbf1dc">
        <div class="num"><?= (int) ($c['pendente'] ?? 0) ?></div>
        <div class="lbl">Novas solicitações</div>
      </div>
      <div class="stat" style="--accent:var(--teal);--accent-bg:var(--teal-bg)">
        <div class="num"><?= (int) (($c['aceito'] ?? 0) + ($c['em_andamento'] ?? 0)) ?></div>
        <div class="lbl">Serviços ativos</div>
      </div>
      <div class="stat" style="--accent:var(--imbel-emerald);--accent-bg:#ecfdf5">
        <div class="num"><?= $fmt(($ganhos ?? [])['total_recebido'] ?? 0) ?></div>
        <div class="lbl">Total recebido</div>
      </div>
      <div class="stat" style="--accent:var(--imbel-navy);--accent-bg:#e8eef5">
        <div class="num"><?= (int) (($ganhos ?? [])['qtd'] ?? 0) ?></div>
        <div class="lbl">Pagamentos capturados</div>
      </div>
    <?php else: ?>
      <div class="stat" style="--accent:var(--imbel-navy);--accent-bg:#e8eef5">
        <div class="num"><?= (int) ($c['usuarios'] ?? 0) ?></div>
        <div class="lbl">Usuários</div>
      </div>
      <div class="stat" style="--accent:var(--imbel-emerald);--accent-bg:#ecfdf5">
        <div class="num"><?= $fmt(($totais ?? [])['comissao'] ?? 0) ?></div>
        <div class="lbl">Comissão da plataforma</div>
      </div>
      <div class="stat" style="--accent:var(--teal);--accent-bg:var(--teal-bg)">
        <div class="num"><?= $fmt(($totais ?? [])['liquido'] ?? 0) ?></div>
        <div class="lbl">Pago a prestadores</div>
      </div>
      <div class="stat" style="--accent:var(--imbel-gold);--accent-bg:#fbf1dc">
        <div class="num"><?= (int) (($totais ?? [])['qtd'] ?? 0) ?></div>
        <div class="lbl">Transações capturadas</div>
      </div>
    <?php endif; ?>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3><svg class="ic"><use href="#i-doc"/></svg>
        <?php if ($role === 'prestador'): ?>Solicitações e serviços recentes
        <?php elseif ($role === 'admin'): ?>Últimos pagamentos
        <?php else: ?>Meus agendamentos recentes
        <?php endif; ?>
      </h3>
    </div>
    <div class="tbl-wrap">
      <?php if ($role === 'admin'): ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Prestador</th>
              <th>Bruto</th>
              <th>Comissão</th>
              <th>Líquido</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (($pagamentos ?? []) as $p): ?>
              <tr>
                <td><a href="<?= base_url('agendamentos/' . $p['agendamento_id']) ?>">#<?= (int) $p['agendamento_id'] ?></a></td>
                <td><?= esc($p['cliente_nome']) ?></td>
                <td><?= esc($p['prestador_nome']) ?></td>
                <td class="money"><?= $fmt($p['valor_bruto']) ?></td>
                <td class="money"><?= $fmt($p['valor_comissao']) ?></td>
                <td class="money"><?= $fmt($p['valor_liquido_prestador']) ?></td>
                <td><span class="tag <?= $p['status'] === 'capturado' ? 'green' : 'gold' ?>"><?= esc($p['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($pagamentos)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--ink-500)">Nenhum pagamento ainda.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Tipo</th>
              <th><?= $role === 'prestador' ? 'Cliente' : 'Prestador' ?></th>
              <th>Data</th>
              <th>Valor</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php
              $lista = $role === 'prestador'
                ? array_merge($solicitacoes ?? [], array_slice($agendamentos ?? [], 0, 5))
                : ($agendamentos ?? []);
              $seen = [];
            ?>
            <?php foreach ($lista as $a): ?>
              <?php if (isset($seen[$a['id']])) continue; $seen[$a['id']] = true; ?>
              <tr>
                <td><?= (int) $a['id'] ?></td>
                <td><?= esc($tiposServico[$a['tipo_servico']]['label'] ?? $a['tipo_servico']) ?></td>
                <td><?= esc($role === 'prestador' ? ($a['cliente_nome'] ?? '') : ($a['prestador_nome'] ?? '')) ?></td>
                <td><?= esc(date('d/m/Y', strtotime($a['data_servico']))) ?> <?= esc(substr((string) $a['hora_inicio'], 0, 5)) ?></td>
                <td class="money"><?= $fmt($a['valor_total']) ?></td>
                <td><span class="tag <?= esc($sc[$a['status']] ?? 'gray') ?>"><?= esc($sl[$a['status']] ?? $a['status']) ?></span></td>
                <td><a class="btn btn-ghost" href="<?= base_url('agendamentos/' . $a['id']) ?>">Abrir</a></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($lista)): ?>
              <tr><td colspan="7" style="text-align:center;color:var(--ink-500)">Nenhum agendamento ainda.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
