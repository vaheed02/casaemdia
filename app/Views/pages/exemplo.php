<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Página exemplo</h2>
      <p>Modelo de tela interna com painéis, tabela e botões no padrão ACI</p>
    </div>
    <div style="display:flex;gap:8px">
      <button class="btn btn-primary" type="button"><svg class="ic"><use href="#i-plus"/></svg>Incluir</button>
      <button class="btn btn-ghost" type="button"><svg class="ic"><use href="#i-edit"/></svg>Editar</button>
    </div>
  </div>

  <div class="toolbar">
    <button class="chip on" type="button">Todos</button>
    <button class="chip" type="button">Ativos</button>
    <button class="chip" type="button">Inativos</button>
    <div class="spacer"></div>
    <button class="btn btn-ghost" type="button"><svg class="ic"><use href="#i-search"/></svg>Filtrar</button>
  </div>

  <div class="panel">
    <div class="panel-head">
      <h3><svg class="ic"><use href="#i-doc"/></svg>Registros de exemplo</h3>
    </div>
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Status</th>
            <th style="text-align:right">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="code-cell">1</td>
            <td><span class="strong">Item demonstrativo A</span><br><span class="muted">Descrição do registro</span></td>
            <td><span class="tag green">Ativo</span></td>
            <td style="text-align:right">
              <div class="row-actions" style="justify-content:flex-end">
                <button type="button" title="Editar"><svg class="ic"><use href="#i-edit"/></svg></button>
              </div>
            </td>
          </tr>
          <tr>
            <td class="code-cell">2</td>
            <td><span class="strong">Item demonstrativo B</span><br><span class="muted">Outro registro</span></td>
            <td><span class="tag amber">Pendente</span></td>
            <td style="text-align:right">
              <div class="row-actions" style="justify-content:flex-end">
                <button type="button" title="Editar"><svg class="ic"><use href="#i-edit"/></svg></button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>