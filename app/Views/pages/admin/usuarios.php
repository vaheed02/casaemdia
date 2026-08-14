<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<section class="view active">
  <div class="page-title-row">
    <div class="t">
      <h2>Usuários</h2>
      <p>Clientes, prestadores e administradores</p>
    </div>
  </div>

  <div class="panel">
    <div class="tbl-wrap">
      <table>
        <thead>
          <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Perfil</th><th>Ativo</th></tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= (int) $u['id'] ?></td>
              <td><?= esc($u['nome']) ?></td>
              <td><?= esc($u['email']) ?></td>
              <td><?= esc($u['telefone'] ?? '—') ?></td>
              <td><span class="tag <?= $u['role'] === 'admin' ? 'blue' : ($u['role'] === 'prestador' ? 'teal' : 'green') ?>"><?= esc($u['role']) ?></span></td>
              <td><?= $u['ativo'] ? 'Sim' : 'Não' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
