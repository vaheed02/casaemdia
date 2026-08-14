<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0A1F3D">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <title>Entrar — <?= esc($appNome ?? 'Casa em Dia') ?></title>
    <link rel="icon" type="image/png" href="<?= esc($appIcon ?? base_url('assets/favicon-casa-em-dia.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
    <style>
      body.login-page{min-height:100vh;min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:max(16px, env(safe-area-inset-top)) 16px max(16px, env(safe-area-inset-bottom));background:var(--bg)}
      .login-wrap{width:100%;max-width:920px;display:grid;grid-template-columns:1.1fr .9fr;background:var(--surface);border:1px solid var(--border);border-radius:20px;overflow:hidden;box-shadow:var(--shadow-lg)}
      .login-form{padding:40px 36px}
      .login-brand{display:flex;align-items:center;gap:12px;margin-bottom:28px}
      .login-brand img{width:auto;height:72px;max-width:240px;object-fit:contain}
      .login-brand h1{font-size:20px;color:var(--imbel-navy);font-weight:700}
      .login-brand p{font-size:12px;color:var(--ink-500);margin-top:2px}
      .login-brand-full{justify-content:center;margin-bottom:12px}
      .login-brand-full img{height:96px;max-width:min(320px,90%);filter:drop-shadow(0 4px 14px rgba(10,31,61,.12))}
      .login-aside{background:var(--imbel-navy);color:var(--imbel-text);padding:40px 32px;display:flex;flex-direction:column;justify-content:space-between}
      .login-aside h2{color:#fff;font-size:22px;margin-bottom:10px}
      .login-aside p{font-size:13px;line-height:1.6;color:var(--imbel-text-muted)}
      .login-aside ol{margin:16px 0 0 18px;font-size:13px;line-height:1.7;color:var(--imbel-text)}
      .alert{padding:12px 14px;border-radius:10px;font-size:13px;margin-bottom:16px}
      .alert-error{background:var(--red-bg);color:var(--red);border:1px solid #f5c2c7}
      .alert-success{background:var(--green-bg);color:var(--green);border:1px solid #a7f3d0}
      .login-form .input,.login-form .btn{min-height:46px;font-size:16px}
      .login-form .btn{width:100%;justify-content:center}
      @media (max-width:800px){.login-wrap{grid-template-columns:1fr}.login-aside{display:none}.login-form{padding:28px 20px}}
    </style>
</head>
<body class="login-page">

<div class="login-wrap">
  <div class="login-form">
    <div class="login-brand login-brand-full">
      <img src="<?= esc($appLogo ?? base_url('assets/logo-casa-em-dia.png')) ?>" alt="<?= esc($appNome ?? 'Casa em Dia') ?>">
    </div>
    <p style="font-size:13px;color:var(--ink-500);margin:-8px 0 20px">Serviços sob demanda para casa e exterior</p>

    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <form action="<?= base_url('login/autenticar') ?>" method="POST">
      <?= csrf_field() ?>
      <?php if (! empty($redirect)): ?>
        <input type="hidden" name="redirect" value="<?= esc($redirect) ?>">
      <?php endif; ?>

      <div class="field">
        <label>E-mail</label>
        <input class="input" type="email" name="email" required placeholder="seu@email.com" value="<?= esc(old('email') ?? '') ?>">
      </div>

      <div class="field">
        <label>Senha</label>
        <input class="input" type="password" name="senha" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn btn-green" style="width:100%;justify-content:center;margin-top:8px">Entrar</button>
    </form>

    <p class="hint" style="margin-top:18px;text-align:center">
      Não tem conta? <a href="<?= base_url('cadastro') ?>" style="color:var(--imbel-emerald);font-weight:600">Criar conta</a>
      · <a href="<?= base_url('/') ?>" style="color:var(--ink-500);font-weight:600">Início</a>
    </p>
    <p class="hint" style="margin-top:8px;text-align:center;font-size:12px">
      Demo: <code>cliente@demo.com</code> / <code>prestador@demo.com</code> / <code>admin@demo.com</code> — senha <code>demo123</code>
    </p>
  </div>

  <div class="login-aside">
    <div>
      <span class="tag green" style="margin-bottom:14px">Marketplace</span>
      <h2>Como funciona</h2>
      <ol>
        <li>Cliente agenda o serviço</li>
        <li>Prestador aceita ou rejeita</li>
        <li>Serviço é executado</li>
        <li>Cliente confirma</li>
        <li>Gateway libera o pagamento</li>
        <li>Prestador recebe e o app fica com a comissão</li>
      </ol>
    </div>
    <p style="font-size:11px;color:var(--imbel-text-muted)">&copy; <?= date('Y') ?> Casa em Dia</p>
  </div>
</div>

</body>
</html>
