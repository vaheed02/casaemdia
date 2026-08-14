<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.'); ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Pagamento do agendamento #<?= (int) $ag['id'] ?></h2>
      <p>Ambiente de teste (gateway mock) — simula o checkout do Mercado Pago</p>
    </div>
  </div>

  <div class="panel" style="max-width:520px">
    <div class="panel-head"><h3>Resumo</h3></div>
    <div class="panel-body">
      <div class="field"><label>Serviço</label><div><?= esc($tiposServico[$ag['tipo_servico']]['label'] ?? $ag['tipo_servico']) ?></div></div>
      <div class="field"><label>Prestador</label><div><?= esc($ag['prestador_nome']) ?></div></div>
      <div class="field"><label>Data</label><div><?= esc(date('d/m/Y', strtotime($ag['data_servico']))) ?> às <?= esc(substr((string) $ag['hora_inicio'], 0, 5)) ?></div></div>
      <div class="field"><label>Valor bruto</label><div class="money" style="font-size:22px;color:var(--imbel-emerald)"><?= $fmt($pagamento['valor_bruto']) ?></div></div>
      <div class="field"><label>Comissão app (<?= esc($ag['comissao_percentual']) ?>%)</label><div class="money"><?= $fmt($pagamento['valor_comissao']) ?></div></div>
      <div class="field"><label>Líquido prestador</label><div class="money"><?= $fmt($pagamento['valor_liquido_prestador']) ?></div></div>

      <p class="hint" style="margin:14px 0">
        Ao confirmar, o valor fica <strong>autorizado/retido</strong> na plataforma.
        O prestador só poderá aceitar depois deste passo. Após a execução e sua confirmação,
        a comissão fica com o app e o líquido entra na fila de repasse.
      </p>

      <div class="btn-row">
        <form method="post" action="<?= base_url('pagamentos/mock/' . $ag['id'] . '/confirmar') ?>">
          <?= csrf_field() ?>
          <button class="btn btn-green" type="submit">
            <svg class="ic"><use href="#i-money"/></svg>
            Pagar <?= $fmt($pagamento['valor_bruto']) ?> (simular)
          </button>
        </form>
        <a class="btn btn-ghost" href="<?= base_url('agendamentos/' . $ag['id']) ?>">Voltar ao agendamento</a>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
