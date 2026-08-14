<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Meu perfil de prestador</h2>
      <p>Tipos de serviço, valores e área de atendimento</p>
    </div>
  </div>

  <div class="panel">
    <div class="panel-body">
      <form method="post" action="<?= base_url('prestador/perfil') ?>">
        <?= csrf_field() ?>

        <div class="field">
          <label>Tipos de serviço</label>
          <div class="tipo-check-grid">
            <?php foreach ($tiposServico as $key => $info): ?>
              <label class="tipo-check">
                <input type="checkbox" name="tipos[]" value="<?= esc($key) ?>"
                  <?= in_array($key, $tiposSel ?? [], true) ? 'checked' : '' ?>>
                <span class="tipo-check-ico"><?= esc($info['icone'] ?? '•') ?></span>
                <span>
                  <b><?= esc($info['label']) ?></b>
                  <small><?= esc($info['descricao']) ?></small>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="field">
          <label>Bio</label>
          <textarea class="input" name="bio" rows="3" placeholder="Conte sua experiência e diferenciais"><?= esc($perfil['bio'] ?? '') ?></textarea>
        </div>

        <h3 class="form-subhead">Valores por serviço (R$)</h3>
        <div class="grid-2">
          <?php foreach ($tiposServico as $key => $info): ?>
            <?php $campo = $info['campo_valor']; ?>
            <div class="field">
              <label><?= esc($info['icone'] . ' ' . $info['label']) ?> <small style="text-transform:none;letter-spacing:0;color:var(--ink-500)">(<?= esc($info['unidade']) ?>)</small></label>
              <input class="input" type="number" step="0.01" min="0" name="<?= esc($campo) ?>"
                value="<?= esc($perfil[$campo] ?? '0') ?>">
            </div>
          <?php endforeach; ?>
          <div class="field">
            <label>Cidade</label>
            <input class="input" name="cidade" value="<?= esc($perfil['cidade'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Bairro</label>
            <input class="input" name="bairro" value="<?= esc($perfil['bairro'] ?? '') ?>">
          </div>
          <div class="field">
            <label>E-mail Mercado Pago (repasse)</label>
            <input class="input" type="email" name="mp_email" placeholder="conta@mercado-pago..." value="<?= esc($perfil['mp_email'] ?? '') ?>">
            <p class="hint">Usado pelo admin para identificar a conta de recebimento do líquido.</p>
          </div>
        </div>

        <label class="check-inline">
          <input type="checkbox" name="disponivel" value="1" <?= ! isset($perfil['disponivel']) || $perfil['disponivel'] ? 'checked' : '' ?>>
          Disponível para novos agendamentos
        </label>

        <button class="btn btn-green" type="submit">Salvar perfil</button>
      </form>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
