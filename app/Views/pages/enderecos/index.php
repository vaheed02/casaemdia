<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Meus endereços</h2>
      <p>Locais onde os serviços serão realizados</p>
    </div>
  </div>

  <div class="grid-2">
    <div class="panel">
      <div class="panel-head"><h3>Cadastrados</h3></div>
      <div class="tbl-wrap">
        <table>
          <thead>
            <tr><th>Título</th><th>Endereço</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($enderecos as $e): ?>
              <tr>
                <td><?= esc($e['titulo']) ?><?= $e['principal'] ? ' ★' : '' ?></td>
                <td><?= esc($e['logradouro']) ?>, <?= esc($e['numero']) ?> — <?= esc($e['bairro']) ?>, <?= esc($e['cidade']) ?>/<?= esc($e['uf']) ?></td>
                <td>
                  <form method="post" action="<?= base_url('enderecos/' . $e['id'] . '/excluir') ?>" onsubmit="return confirm('Excluir?');">
                    <?= csrf_field() ?>
                    <button class="btn btn-ghost" type="submit">Excluir</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($enderecos)): ?>
              <tr><td colspan="3" style="text-align:center;color:var(--ink-500)">Nenhum endereço. Cadastre ao lado.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="panel">
      <div class="panel-head"><h3>Novo endereço</h3></div>
      <div class="panel-body">
        <form method="post" action="<?= base_url('enderecos') ?>">
          <?= csrf_field() ?>
          <div class="field"><label>Título</label><input class="input" name="titulo" placeholder="Casa, Trabalho…" value="Casa"></div>
          <div class="field"><label>CEP</label><input class="input" name="cep" placeholder="00000-000"></div>
          <div class="field"><label>Logradouro</label><input class="input" name="logradouro" required></div>
          <div class="grid-2">
            <div class="field"><label>Número</label><input class="input" name="numero"></div>
            <div class="field"><label>Complemento</label><input class="input" name="complemento"></div>
          </div>
          <div class="field"><label>Bairro</label><input class="input" name="bairro" required></div>
          <div class="grid-2">
            <div class="field"><label>Cidade</label><input class="input" name="cidade" required></div>
            <div class="field"><label>UF</label><input class="input" name="uf" maxlength="2" required placeholder="SP"></div>
          </div>
          <label style="display:flex;gap:8px;align-items:center;margin:10px 0">
            <input type="checkbox" name="principal" value="1"> Principal
          </label>
          <button class="btn btn-green" type="submit">Salvar endereço</button>
        </form>
      </div>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
