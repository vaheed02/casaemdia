<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0A1F3D">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Criar conta — <?= esc($appNome ?? 'Casa em Dia') ?></title>
    <link rel="icon" type="image/png" href="<?= esc($appIcon ?? base_url('assets/favicon-casa-em-dia.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>
      body.login-page{min-height:100vh;min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:max(16px, env(safe-area-inset-top)) 16px max(16px, env(safe-area-inset-bottom));background:var(--bg)}
      .login-wrap{width:100%;max-width:520px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:36px;box-shadow:var(--shadow-lg)}
      .login-brand{display:flex;align-items:center;gap:12px;margin-bottom:24px;flex-wrap:wrap}
      .login-brand img{width:auto;height:80px;max-width:260px;object-fit:contain;filter:drop-shadow(0 4px 12px rgba(10,31,61,.1))}
      .login-brand h1{font-size:20px;color:var(--imbel-navy);font-weight:700}
      .alert-error{padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;background:var(--red-bg);color:var(--red);border:1px solid #f5c2c7}
      .role-pick{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
      .role-pick label{border:1px solid var(--border-strong);border-radius:12px;padding:12px;cursor:pointer;text-align:center;font-size:13px}
      .role-pick input{display:none}
      .role-pick input:checked + span{color:var(--imbel-emerald);font-weight:700}
      .role-pick label:has(input:checked){border-color:var(--imbel-emerald);background:var(--green-bg)}
      .login-wrap .input,.login-wrap .btn{min-height:46px;font-size:16px}
      .login-wrap .btn{width:100%;justify-content:center}
      @media (max-width:520px){.login-wrap{padding:24px 18px}}
    </style>
</head>
<body class="login-page">

<div class="login-wrap">
  <div class="login-brand">
    <img src="<?= esc($appLogo ?? base_url('assets/logo-casa-em-dia.png')) ?>" alt="<?= esc($appNome ?? 'Casa em Dia') ?>">
    <div>
      <h1>Criar conta</h1>
      <p style="font-size:12px;color:var(--ink-500)">Cliente ou prestador</p>
    </div>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <form action="<?= base_url('cadastro') ?>" method="POST">
    <?= csrf_field() ?>

    <div class="field">
      <label>Quero me cadastrar como</label>
      <?php $roleSel = old('role') ?? ($rolePref ?? 'cliente'); ?>
      <div class="role-pick">
        <label><input type="radio" name="role" value="cliente" <?= $roleSel === 'cliente' ? 'checked' : '' ?>><span>Cliente</span><br><small style="color:var(--ink-500)">Agendar serviços</small></label>
        <label><input type="radio" name="role" value="prestador" <?= $roleSel === 'prestador' ? 'checked' : '' ?>><span>Prestador</span><br><small style="color:var(--ink-500)">Serviços diversos</small></label>
      </div>
    </div>

    <div class="field">
      <label>Nome completo</label>
      <input class="input" type="text" name="nome" required value="<?= esc(old('nome') ?? '') ?>">
    </div>

    <div class="field">
      <label>E-mail</label>
      <input class="input" type="email" name="email" required value="<?= esc(old('email') ?? '') ?>">
    </div>

    <div class="field">
      <label>Telefone</label>
      <input class="input" type="text" name="telefone" placeholder="(11) 99999-0000" value="<?= esc(old('telefone') ?? '') ?>">
    </div>

    <div class="field">
      <label>Senha</label>
      <input class="input" type="password" name="senha" required minlength="6" placeholder="mínimo 6 caracteres">
    </div>

    <button type="submit" class="btn btn-green" style="width:100%;justify-content:center;margin-top:8px">Criar conta</button>
  </form>

  <p class="hint" style="margin-top:18px;text-align:center">
    Já tem conta? <a href="<?= base_url('login') ?>" style="color:var(--imbel-emerald);font-weight:600">Entrar</a>
    · <a href="<?= base_url('/') ?>" style="color:var(--ink-500);font-weight:600">Início</a>
  </p>
</div>

</body>
</html>
