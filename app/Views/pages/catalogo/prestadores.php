<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $fmt = static fn ($v) => 'R$ ' . number_format((float) $v, 2, ',', '.'); ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Buscar prestadores</h2>
      <p>Diarista, pets, telhado, piscinas, jardins e hidráulica</p>
    </div>
  </div>

  <form class="toolbar app-filters" method="get" action="<?= base_url('prestadores') ?>">
    <select class="input" name="tipo">
      <option value="">Todos os tipos</option>
      <?php foreach ($tiposServico as $key => $info): ?>
        <option value="<?= esc($key) ?>" <?= ($filtroTipo ?? '') === $key ? 'selected' : '' ?>>
          <?= esc(($info['icone'] ?? '') . ' ' . $info['label']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input class="input" type="text" name="cidade" placeholder="Cidade" value="<?= esc($filtroCidade ?? '') ?>">
    <button class="btn btn-green" type="submit"><svg class="ic"><use href="#i-search"/></svg>Filtrar</button>
  </form>

  <div class="card-grid" style="margin-top:16px">
    <?php foreach ($prestadores as $p): ?>
      <?php
        $tipos = array_filter(array_map('trim', explode(',', (string) $p['tipos_servico'])));
        $labels = array_map(static fn ($t) => $tiposServico[$t]['label'] ?? $t, $tipos);
      ?>
      <div class="person-card">
        <h3><?= esc($p['nome']) ?></h3>
        <div class="meta">
          <?= esc(implode(' · ', $labels)) ?>
          <?php if (! empty($p['cidade'])): ?> · <?= esc($p['bairro'] ? $p['bairro'] . ', ' : '') ?><?= esc($p['cidade']) ?><?php endif; ?>
        </div>
        <?php if ((float) $p['avaliacao_media'] > 0): ?>
          <div class="meta">★ <?= number_format((float) $p['avaliacao_media'], 1, ',', '.') ?> (<?= (int) $p['total_avaliacoes'] ?>)</div>
        <?php endif; ?>
        <?php if (! empty($p['bio'])): ?>
          <p class="card-bio"><?= esc(mb_strimwidth($p['bio'], 0, 120, '…')) ?></p>
        <?php endif; ?>
        <div class="price price-list">
          <?php foreach ($tipos as $t): ?>
            <?php
              $campo = $tiposServico[$t]['campo_valor'] ?? null;
              $val   = $campo ? (float) ($p[$campo] ?? 0) : 0;
              if ($val <= 0) {
                  continue;
              }
            ?>
            <span><?= esc($tiposServico[$t]['label'] ?? $t) ?> <?= $fmt($val) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="btn-row" style="margin-top:12px">
          <?php foreach ($tipos as $t): ?>
            <?php
              $campo = $tiposServico[$t]['campo_valor'] ?? null;
              $val   = $campo ? (float) ($p[$campo] ?? 0) : 0;
              if ($val <= 0) {
                  continue;
              }
              $cls = $t === array_values($tipos)[0] ? 'btn-green' : 'btn-ghost';
            ?>
            <a class="btn <?= $cls ?>" href="<?= base_url('agendamentos/novo?prestador=' . $p['usuario_id'] . '&tipo=' . urlencode($t)) ?>">
              Agendar <?= esc($tiposServico[$t]['label'] ?? $t) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($prestadores)): ?>
    <div class="panel" style="margin-top:16px">
      <div class="panel-body" style="text-align:center;color:var(--ink-500)">Nenhum prestador encontrado com esses filtros.</div>
    </div>
  <?php endif; ?>
</section>
<?= $this->endSection() ?>
