<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.');
$tiposCfg = $tiposCfg ?? ($tiposServico ?? []);
$duracaoDefault = isset($tiposCfg[$tipo]['duracao_padrao']) ? $tiposCfg[$tipo]['duracao_padrao'] : 4;
?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Novo agendamento</h2>
      <p>Ao confirmar, o valor é <strong>reservado</strong> no gateway e só é liberado depois da sua confirmação do serviço.</p>
    </div>
  </div>

  <?php if (empty($enderecos)): ?>
    <div class="alert alert-error">
      Você precisa cadastrar um endereço antes de agendar.
      <a href="<?= base_url('enderecos') ?>" style="font-weight:700;margin-left:6px">Cadastrar endereço</a>
    </div>
  <?php endif; ?>

  <div class="panel">
    <div class="panel-body">
      <form method="post" action="<?= base_url('agendamentos') ?>">
        <?= csrf_field() ?>

        <div class="grid-2">
          <div class="field">
            <label>Prestador</label>
            <select class="input" name="prestador_id" required id="prestadorSelect">
              <option value="">Selecione…</option>
              <?php foreach ($prestadores as $p): ?>
                <?php
                  $priceMap = [];
                  foreach ($tiposCfg as $tk => $ti) {
                      $priceMap[$tk] = (float) ($p[$ti['campo_valor']] ?? 0);
                  }
                ?>
                <option value="<?= (int) $p['usuario_id'] ?>"
                  data-prices="<?= esc(json_encode($priceMap, JSON_UNESCAPED_UNICODE)) ?>"
                  data-tipos="<?= esc($p['tipos_servico']) ?>"
                  <?= (int) $prestadorId === (int) $p['usuario_id'] ? 'selected' : '' ?>>
                  <?= esc($p['nome']) ?> — <?= esc($p['cidade'] ?: 'sem cidade') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Tipo de serviço</label>
            <select class="input" name="tipo_servico" id="tipoSelect" required>
              <?php foreach ($tiposCfg as $key => $info): ?>
                <option value="<?= esc($key) ?>"
                  data-duracao="<?= esc($info['duracao_padrao'] ?? 4) ?>"
                  <?= ($tipo ?? '') === $key ? 'selected' : '' ?>>
                  <?= esc(($info['icone'] ?? '') . ' ' . $info['label']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Endereço do serviço</label>
            <select class="input" name="endereco_id" required>
              <option value="">Selecione…</option>
              <?php foreach ($enderecos as $e): ?>
                <option value="<?= (int) $e['id'] ?>">
                  <?= esc($e['titulo']) ?> — <?= esc($e['logradouro']) ?>, <?= esc($e['numero']) ?> · <?= esc($e['bairro']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field">
            <label>Data</label>
            <input class="input" type="date" name="data_servico" required min="<?= date('Y-m-d') ?>" value="<?= esc(old('data_servico') ?? '') ?>">
          </div>

          <div class="field">
            <label>Horário</label>
            <input class="input" type="time" name="hora_inicio" required value="<?= esc(old('hora_inicio') ?? '09:00') ?>">
          </div>

          <div class="field">
            <label>Duração (horas)</label>
            <input class="input" type="number" name="duracao_horas" id="duracaoInput" min="0.5" step="0.5" value="<?= esc(old('duracao_horas') ?? $duracaoDefault) ?>">
          </div>
        </div>

        <div class="field">
          <label>Observações para o prestador</label>
          <textarea class="input" name="observacoes_cliente" rows="3" placeholder="Ex.: acesso, materiais, preferências…"><?= esc(old('observacoes_cliente') ?? '') ?></textarea>
        </div>

        <div class="field">
          <label>Valor a pagar (R$) — opcional</label>
          <input class="input" type="number" name="valor_total" id="valorTotalInput" min="1" step="0.01"
                 placeholder="Deixe vazio para usar o preço do prestador"
                 value="<?= esc(old('valor_total') ?? '') ?>">
          <p class="hint">Para teste use <strong>5.00</strong>. Comissão da plataforma: <?= (float) $comissaoPct ?>%.</p>
        </div>

        <div class="panel value-summary">
          <div class="value-summary-row">
            <div>
              <div class="value-label">Valor do serviço</div>
              <div class="money" id="valorBox"><?= $fmt($valorSugerido) ?></div>
            </div>
            <div>
              <div class="value-label">Comissão (<?= (float) $comissaoPct ?>%)</div>
              <div class="money" id="comissaoBox">—</div>
            </div>
            <div>
              <div class="value-label">Prestador recebe</div>
              <div class="money" id="liquidoBox">—</div>
            </div>
          </div>
          <p class="hint" style="margin-top:10px">
            Ao confirmar você será levado ao <strong>pagamento</strong> (Mercado Pago ou checkout de teste).
            O valor fica <strong>retido na plataforma</strong> até o serviço ser confirmado; a comissão fica com o app e o líquido é repassado ao prestador pelo admin.
          </p>
        </div>

        <div class="btn-row">
          <button class="btn btn-green" type="submit" <?= empty($enderecos) ? 'disabled' : '' ?>>Agendar e pagar</button>
          <a class="btn btn-ghost" href="<?= base_url('prestadores') ?>">Voltar</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function(){
  const pct = <?= json_encode((float) $comissaoPct) ?>;
  const prest = document.getElementById('prestadorSelect');
  const tipo = document.getElementById('tipoSelect');
  const duracao = document.getElementById('duracaoInput');
  const valorBox = document.getElementById('valorBox');
  const comissaoBox = document.getElementById('comissaoBox');
  const liquidoBox = document.getElementById('liquidoBox');

  function money(v){
    return 'R$ ' + Number(v).toLocaleString('pt-BR', {minimumFractionDigits:2, maximumFractionDigits:2});
  }

  function pricesOf(opt){
    try { return JSON.parse(opt.dataset.prices || '{}'); } catch(e){ return {}; }
  }

  function recalc(){
    const opt = prest.options[prest.selectedIndex];
    if (!opt || !opt.value) {
      valorBox.textContent = 'R$ 0,00';
      comissaoBox.textContent = '—';
      liquidoBox.textContent = '—';
      return;
    }
    const map = pricesOf(opt);
    const v = parseFloat(map[tipo.value] || 0);
    const com = Math.round(v * pct) / 100;
    const liq = Math.round((v - com) * 100) / 100;
    valorBox.textContent = money(v);
    comissaoBox.textContent = money(com);
    liquidoBox.textContent = money(liq);
  }

  tipo.addEventListener('change', function(){
    const d = parseFloat(tipo.options[tipo.selectedIndex].dataset.duracao || 4);
    if (duracao && !duracao.dataset.userEdited) duracao.value = d;
    recalc();
  });
  if (duracao) {
    duracao.addEventListener('input', () => { duracao.dataset.userEdited = '1'; });
  }
  prest.addEventListener('change', recalc);
  recalc();
})();
</script>
<?= $this->endSection() ?>
