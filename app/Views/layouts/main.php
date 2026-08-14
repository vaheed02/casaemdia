<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <script>
    (function () {
      try {
        if (localStorage.getItem('pm-theme') === 'dark') {
          document.documentElement.setAttribute('data-theme', 'dark');
        }
      } catch (e) {}
    })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0A1F3D">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title><?= esc($appSigla) ?> · <?= esc($title ?? 'Painel') ?> — <?= esc($appNome) ?></title>
    <link rel="icon" type="image/png" href="<?= esc($appIcon ?? base_url('assets/favicon-casa-em-dia.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>
      .alert{padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;border:1px solid transparent}
      .alert-error{background:var(--red-bg);color:var(--red);border-color:#f5c2c7}
      .alert-success{background:var(--green-bg);color:var(--green);border-color:#a7f3d0}
      .tag.gold{background:var(--amber-bg);color:var(--amber)}
      .tag.teal{background:var(--teal-bg);color:var(--teal)}
      .tag.gray{background:var(--surface-2);color:var(--ink-500)}
      .flow-steps{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0 20px}
      .flow-steps .step{font-size:12px;padding:6px 10px;border-radius:999px;background:var(--surface-2);color:var(--ink-500);border:1px solid var(--border)}
      .flow-steps .step.on{background:var(--green-bg);color:var(--green);border-color:#a7f3d0;font-weight:600}
      .flow-steps .step.done{background:var(--blue-bg);color:var(--blue)}
      .card-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:14px}
      .person-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:16px;box-shadow:var(--shadow-sm)}
      .person-card h3{font-size:16px;margin-bottom:4px}
      .person-card .meta{font-size:12px;color:var(--ink-500);margin-bottom:10px}
      .person-card .price{font-weight:700;color:var(--imbel-emerald);font-size:15px}
      .btn-row{display:flex;flex-wrap:wrap;gap:8px}
      .money{font-variant-numeric:tabular-nums;font-weight:600}
      .card-bio{font-size:13px;color:var(--ink-700);margin:8px 0 12px}
      .price-list{display:flex;flex-direction:column;gap:4px;font-size:13px}
      .price-list span{font-weight:600}
      .app-filters{gap:10px;flex-wrap:wrap}
      .app-filters .input{max-width:100%;flex:1 1 160px}
      .tipo-check-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;margin-top:8px}
      .tipo-check{display:flex;gap:10px;align-items:flex-start;border:1px solid var(--border);border-radius:12px;padding:12px;cursor:pointer;background:var(--surface);transition:border-color var(--t),background var(--t)}
      .tipo-check:has(input:checked){border-color:var(--imbel-emerald);background:var(--green-bg)}
      .tipo-check input{margin-top:4px}
      .tipo-check-ico{font-size:1.25rem;line-height:1}
      .tipo-check b{display:block;font-size:13.5px;color:var(--ink-900)}
      .tipo-check small{display:block;color:var(--ink-500);font-size:12px;margin-top:2px}
      .form-subhead{font-size:14px;color:var(--imbel-navy);margin:8px 0 12px}
      .check-inline{display:flex;gap:8px;align-items:center;margin:12px 0}
      .value-summary{background:var(--surface-2);margin:12px 0;padding:14px;border-radius:12px}
      .value-summary-row{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}
      .value-label{font-size:12px;color:var(--ink-500)}
      #valorBox{font-size:22px;color:var(--imbel-emerald)}
    </style>
</head>
<body class="app-shell">

<svg width="0" height="0" style="position:absolute" aria-hidden="true">
  <symbol id="i-dash" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></symbol>
  <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
  <symbol id="i-logout" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></symbol>
  <symbol id="i-menu" viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></symbol>
  <symbol id="i-collapse" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></symbol>
  <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></symbol>
  <symbol id="i-bell" viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></symbol>
  <symbol id="i-doc" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></symbol>
  <symbol id="i-cog" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></symbol>
  <symbol id="i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="4.22" y1="4.22" x2="6.34" y2="6.34"/><line x1="17.66" y1="17.66" x2="19.78" y2="19.78"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/><line x1="4.22" y1="19.78" x2="6.34" y2="17.66"/><line x1="17.66" y1="6.34" x2="19.78" y2="4.22"/></symbol>
  <symbol id="i-moon" viewBox="0 0 24 24"><path d="M21 14.5A8.5 8.5 0 0 1 9.5 3 7 7 0 1 0 21 14.5z"/></symbol>
  <symbol id="i-plus" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></symbol>
  <symbol id="i-edit" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4z"/></symbol>
  <symbol id="i-map" viewBox="0 0 24 24"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></symbol>
  <symbol id="i-money" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></symbol>
  <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></symbol>
  <symbol id="i-play" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></symbol>
</svg>

<aside class="sidebar">
  <div class="sidebar-body">
    <div class="brand">
      <img src="<?= esc($appLogo ?? base_url('assets/logo-casa-em-dia.png')) ?>" alt="<?= esc($appNome) ?>" class="brand-logo brand-logo-sm">
      <div class="brand-text">
        <strong><?= esc($appNome) ?></strong>
        <span class="brand-sub"><?= esc($appDescricao) ?></span>
      </div>
    </div>

    <nav class="nav">
      <?php foreach ($menu as $group): ?>
        <div class="nav-group">
          <div class="nav-label"><?= esc($group['label']) ?></div>
          <?php foreach ($group['items'] as $item): ?>
            <a class="nav-item <?= ($activePage ?? '') === $item['page'] ? 'active' : '' ?>" href="<?= base_url($item['url']) ?>">
              <svg class="ic"><use href="#<?= esc($item['icon']) ?>"/></svg>
              <span class="label"><?= esc($item['label']) ?></span>
              <?php if (! empty($item['badge'])): ?>
                <span class="badge"><?= esc($item['badge']) ?></span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-imbel">
      <p class="sidebar-imbel-text">Agenda → Aceite → Serviço → Pagamento</p>
    </div>
  </div>

  <div class="side-foot">
    <div class="avatar"><?= esc($usuarioIniciais) ?></div>
    <div class="u-info">
      <b><?= esc($usuarioNome) ?></b>
      <small><?= esc(ucfirst($usuarioRole)) ?></small>
    </div>
  </div>
</aside>

<div class="scrim" id="scrim"></div>

<div class="main">
  <header class="topbar">
    <button class="icon-btn hamburger" id="btnMenu" aria-label="Abrir menu"><svg class="ic"><use href="#i-menu"/></svg></button>
    <button class="icon-btn collapse-btn" id="btnCollapse" aria-label="Recolher menu"><svg class="ic"><use href="#i-collapse"/></svg></button>
    <div class="page-head">
      <span class="crumb"><?= esc($appSigla) ?> &nbsp;/&nbsp; <span id="crumb"><?= esc($title ?? 'Painel') ?></span></span>
      <h1 id="topTitle"><?= esc($title ?? 'Painel') ?></h1>
    </div>
    <div class="search">
      <svg class="ic"><use href="#i-search"/></svg>
      <input type="text" placeholder="Buscar no sistema…">
    </div>
    <button class="theme-toggle-topbar" id="themeToggleTop" type="button" data-theme-toggle aria-label="Alternar tema" title="Alternar tema">
      <span class="theme-toggle-topbar-icons" aria-hidden="true">
        <svg class="ic theme-ic-sun"><use href="#i-sun"/></svg>
        <svg class="ic theme-ic-moon"><use href="#i-moon"/></svg>
      </span>
      <span class="theme-toggle-topbar-label">
        <span class="theme-label-light">Claro</span>
        <span class="theme-label-dark">Escuro</span>
      </span>
    </button>
    <button class="icon-btn" aria-label="Notificações"><span class="dot"></span><svg class="ic"><use href="#i-bell"/></svg></button>
  </header>

  <main class="content">
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
  </main>
</div>

<?php
  $roleNav = $usuarioRole ?? '';
  $active  = $activePage ?? '';
  $isCliente = in_array($roleNav, ['cliente', 'admin'], true);
  $isPrest   = in_array($roleNav, ['prestador', 'admin'], true);
?>
<nav class="app-bottom-nav" aria-label="Navegação principal mobile">
  <a href="<?= base_url('dashboard') ?>" class="<?= $active === 'dashboard' ? 'on' : '' ?>">
    <svg class="ic"><use href="#i-dash"/></svg>
    <small>Painel</small>
  </a>
  <?php if ($isCliente): ?>
    <a href="<?= base_url('prestadores') ?>" class="<?= $active === 'buscar' ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-search"/></svg>
      <small>Buscar</small>
    </a>
    <a href="<?= base_url('agendamentos') ?>" class="<?= in_array($active, ['meus-agendamentos', 'agendar'], true) ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-doc"/></svg>
      <small>Agenda</small>
    </a>
  <?php elseif ($isPrest): ?>
    <a href="<?= base_url('prestador/solicitacoes') ?>" class="<?= $active === 'solicitacoes' ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-bell"/></svg>
      <small>Pedidos</small>
    </a>
    <a href="<?= base_url('prestador/servicos') ?>" class="<?= $active === 'meus-servicos' ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-doc"/></svg>
      <small>Serviços</small>
    </a>
  <?php endif; ?>
  <?php if ($roleNav === 'admin'): ?>
    <a href="<?= base_url('admin/usuarios') ?>" class="<?= str_starts_with((string) $active, 'admin') ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-users"/></svg>
      <small>Admin</small>
    </a>
  <?php elseif ($isPrest): ?>
    <a href="<?= base_url('prestador/perfil') ?>" class="<?= $active === 'perfil-prestador' ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-users"/></svg>
      <small>Perfil</small>
    </a>
  <?php else: ?>
    <a href="<?= base_url('enderecos') ?>" class="<?= $active === 'enderecos' ? 'on' : '' ?>">
      <svg class="ic"><use href="#i-map"/></svg>
      <small>Endereços</small>
    </a>
  <?php endif; ?>
  <a href="<?= base_url('logout') ?>">
    <svg class="ic"><use href="#i-logout"/></svg>
    <small>Sair</small>
  </a>
</nav>

<script>
(function(){
  const body = document.body;
  const scrim = document.getElementById('scrim');
  const THEME_KEY = 'pm-theme';

  window.setPmTheme = function(theme) {
    const dark = theme === 'dark';
    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
    try { localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light'); } catch (e) {}
    document.querySelectorAll('[data-theme-toggle]').forEach(el => {
      el.setAttribute('aria-pressed', dark ? 'true' : 'false');
    });
  };

  window.togglePmTheme = function() {
    const dark = document.documentElement.getAttribute('data-theme') === 'dark';
    window.setPmTheme(dark ? 'light' : 'dark');
  };

  document.querySelectorAll('[data-theme-toggle]').forEach(el => {
    el.addEventListener('click', e => {
      e.preventDefault();
      window.togglePmTheme();
    });
  });

  const saved = (function(){ try { return localStorage.getItem(THEME_KEY); } catch(e){ return null; } })();
  if (saved === 'dark' || saved === 'light') window.setPmTheme(saved);

  const btnCollapse = document.getElementById('btnCollapse');
  const btnMenu = document.getElementById('btnMenu');
  if (btnCollapse) btnCollapse.addEventListener('click', () => body.classList.toggle('collapsed'));
  if (btnMenu) btnMenu.addEventListener('click', () => body.classList.toggle('nav-open'));
  if (scrim) scrim.addEventListener('click', () => body.classList.remove('nav-open'));

  // Fecha menu drawer ao navegar (mobile)
  document.querySelectorAll('.sidebar .nav-item').forEach(a => {
    a.addEventListener('click', () => body.classList.remove('nav-open'));
  });
})();
</script>

<?= $this->renderSection('scripts') ?>

</body>
</html>