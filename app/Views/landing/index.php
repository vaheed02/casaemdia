<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a1f3d">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title><?= esc($appNome) ?> — Serviços sob demanda para casa e exterior</title>
    <meta name="description" content="Casa em Dia: contrate diarista, passeador, telhado, piscinas, jardins e hidráulica com segurança. Agende, acompanhe e pague com transparência.">
    <link rel="icon" type="image/png" href="<?= esc($appIcon ?? base_url('assets/favicon-casa-em-dia.png')) ?>">
    <link rel="stylesheet" href="<?= base_url('css/landing.css') ?>">
</head>
<body class="lp">

<header class="lp-top">
  <div class="lp-container lp-top-inner">
    <a class="lp-brand" href="<?= base_url('/') ?>">
      <img src="<?= esc($appLogo ?? base_url('assets/logo-casa-em-dia.png')) ?>" alt="<?= esc($appNome) ?>" class="lp-brand-logo">
    </a>
    <nav class="lp-nav" aria-label="Principal">
      <a class="lp-link lp-link-hide-sm" href="#servicos">Serviços</a>
      <a class="lp-link lp-link-hide-sm" href="#como-funciona">Como funciona</a>
      <a class="lp-link lp-link-hide-sm" href="#vantagens">Vantagens</a>
      <a class="lp-link lp-link-hide-sm" href="#acessos">Acessos</a>
      <?php if ($logado): ?>
        <a class="lp-btn lp-btn-ghost" href="<?= base_url('dashboard') ?>">Painel</a>
        <a class="lp-btn lp-btn-primary" href="<?= base_url('solicitar') ?>">Solicitar</a>
      <?php else: ?>
        <a class="lp-btn lp-btn-ghost" href="<?= base_url('login') ?>">Entrar</a>
        <a class="lp-btn lp-btn-primary" href="<?= base_url('solicitar') ?>">Solicitar</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php if (session()->getFlashdata('error')): ?>
  <div class="lp-flash lp-flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('success')): ?>
  <div class="lp-flash lp-flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<section class="lp-hero">
  <div class="lp-container lp-hero-grid">
    <div>
      <div class="lp-kicker">Marketplace de serviços residenciais</div>
      <h1>Tudo para sua casa <em>quando você precisa</em></h1>
      <p class="lp-hero-lead">
        Diarista, pets, telhado, piscinas, jardins e hidráulica — profissionais avaliados,
        agenda sob demanda e pagamento protegido até a confirmação do serviço.
      </p>
      <div class="lp-hero-cta">
        <a class="lp-btn lp-btn-primary lp-btn-lg" href="<?= base_url('solicitar') ?>">Solicitar um serviço</a>
        <a class="lp-btn lp-btn-ghost lp-btn-lg" href="<?= base_url('cadastro') ?>">Criar conta grátis</a>
      </div>
      <div class="lp-hero-meta">
        <span><strong><?= count($tipos) ?></strong> tipos de serviço</span>
        <span><strong>Agenda</strong> sob demanda</span>
        <span><strong>Pagamento</strong> protegido</span>
      </div>
    </div>

    <aside class="lp-hero-card" aria-label="Resumo do fluxo">
      <h3>Do pedido ao serviço concluído</h3>
      <div class="lp-steps-mini">
        <div class="lp-step-mini">
          <div class="n">1</div>
          <div>
            <b>Você solicita</b>
            <span>Escolha o tipo de serviço, data, horário e endereço.</span>
          </div>
        </div>
        <div class="lp-step-mini">
          <div class="n">2</div>
          <div>
            <b>O profissional aceita</b>
            <span>Prestadores da categoria recebem e respondem ao pedido.</span>
          </div>
        </div>
        <div class="lp-step-mini">
          <div class="n">3</div>
          <div>
            <b>Serviço e confirmação</b>
            <span>Acompanhe o status e confirme a execução no app.</span>
          </div>
        </div>
        <div class="lp-step-mini">
          <div class="n">4</div>
          <div>
            <b>Pagamento justo</b>
            <span>Prestador recebe o líquido; a plataforma retém a comissão.</span>
          </div>
        </div>
      </div>
    </aside>
  </div>
</section>

<section class="lp-section lp-section-alt" id="servicos">
  <div class="lp-container">
    <div class="lp-section-head">
      <h2>Serviços para o dia a dia e para a casa</h2>
      <p>Um só app para limpeza, pets e manutenções — com clareza de valores e acompanhamento.</p>
    </div>
    <div class="lp-cards lp-cards-6">
      <?php foreach ($tipos as $key => $info): ?>
        <article class="lp-card">
          <div class="lp-card-icon" aria-hidden="true"><?= esc($info['icone'] ?? '•') ?></div>
          <h3><?= esc($info['label']) ?></h3>
          <p><?= esc($info['descricao']) ?></p>
          <ul>
            <li>Cobrança por <?= esc($info['unidade']) ?></li>
            <li>Prestadores com perfil e avaliações</li>
            <li>Status do pedido em tempo real</li>
            <li>Pagamento após confirmação</li>
          </ul>
          <a class="lp-btn lp-btn-primary" style="margin-top:14px;width:100%" href="<?= base_url('solicitar') ?>">
            Solicitar
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="lp-section" id="como-funciona">
  <div class="lp-container">
    <div class="lp-section-head">
      <h2>Como funciona</h2>
      <p>Fluxo simples no celular ou no computador — estilo web app.</p>
    </div>
    <div class="lp-process">
      <div class="lp-process-item">
        <div class="num">01</div>
        <h3>Crie sua conta</h3>
        <p>Cliente para contratar, ou prestador para receber pedidos nas categorias que atende.</p>
      </div>
      <div class="lp-process-item">
        <div class="num">02</div>
        <h3>Solicite o serviço</h3>
        <p>Escolha o profissional e a categoria: diarista, pets, telhado, piscina, jardim ou hidráulica.</p>
      </div>
      <div class="lp-process-item">
        <div class="num">03</div>
        <h3>Acompanhe o status</h3>
        <p>Pendente, aceito, em andamento, concluído — tudo no painel do app.</p>
      </div>
      <div class="lp-process-item">
        <div class="num">04</div>
        <h3>Confirme e pague</h3>
        <p>Você confirma a entrega; o pagamento é liberado com a comissão da plataforma.</p>
      </div>
    </div>
  </div>
</section>

<section class="lp-section lp-section-alt" id="vantagens">
  <div class="lp-container lp-benefits">
    <div class="lp-benefits-copy">
      <h2>Profissionalismo do pedido ao pagamento</h2>
      <p>
        A <?= esc($appNome) ?> conecta quem precisa de ajuda em casa com quem presta o serviço,
        com regras claras e trilha completa de agendamentos.
      </p>
      <ul class="lp-check-list">
        <li>
          <span class="dot">✓</span>
          <div>
            <strong>Vários nichos em um lugar</strong>
            <span>Casa, pets e manutenções sem trocar de app ou de WhatsApp.</span>
          </div>
        </li>
        <li>
          <span class="dot">✓</span>
          <div>
            <strong>Pensado para o celular</strong>
            <span>Interface web app responsiva, com navegação rápida no mobile.</span>
          </div>
        </li>
        <li>
          <span class="dot">✓</span>
          <div>
            <strong>Admin autenticado</strong>
            <span>Usuários, pagamentos e comissões com acesso restrito.</span>
          </div>
        </li>
      </ul>
    </div>
    <div class="lp-stat-grid">
      <div class="lp-stat">
        <b><?= count($tipos) ?></b>
        <span>Categorias de serviço</span>
      </div>
      <div class="lp-stat">
        <b><?= (int) $comissao ?>%</b>
        <span>Comissão padrão da plataforma</span>
      </div>
      <div class="lp-stat">
        <b>App web</b>
        <span>Responsivo no celular e desktop</span>
      </div>
      <div class="lp-stat">
        <b>Admin</b>
        <span>Painel com autenticação</span>
      </div>
    </div>
  </div>
</section>

<section class="lp-section" id="acessos">
  <div class="lp-container">
    <div class="lp-section-head">
      <h2>Por onde começar</h2>
      <p>Escolha o caminho conforme o seu papel na plataforma.</p>
    </div>
    <div class="lp-access">
      <article class="lp-access-card">
        <span class="tag tag-client">Cliente</span>
        <h3>Solicitar um serviço</h3>
        <p>Entre ou cadastre-se, escolha o prestador e a categoria, e envie o pedido.</p>
        <a class="lp-btn lp-btn-primary" href="<?= base_url('solicitar') ?>">Quero contratar</a>
        <a class="lp-link" href="<?= base_url('cadastro') ?>" style="font-weight:600;color:var(--lp-emerald)">Criar conta de cliente →</a>
      </article>

      <article class="lp-access-card">
        <span class="tag tag-pro">Prestador</span>
        <h3>Trabalhe conosco</h3>
        <p>Ative as categorias que você atende, defina valores e receba solicitações.</p>
        <a class="lp-btn lp-btn-dark" href="<?= base_url('cadastro?role=prestador') ?>">Sou prestador</a>
        <a class="lp-link" href="<?= base_url('login') ?>" style="font-weight:600;color:var(--lp-navy)">Já tenho conta →</a>
      </article>

      <article class="lp-access-card">
        <span class="tag tag-admin">Administração</span>
        <h3>Área administrativa</h3>
        <p>Gestão de usuários, pagamentos e comissões. Somente login de administrador.</p>
        <a class="lp-btn lp-btn-ghost" href="<?= base_url('admin-acesso') ?>">Acessar admin</a>
        <a class="lp-link" href="<?= base_url('login') ?>" style="font-weight:600;color:var(--lp-muted)">Entrar na conta →</a>
      </article>
    </div>
  </div>
</section>

<section class="lp-section lp-section-alt">
  <div class="lp-container">
    <div class="lp-cta-band">
      <div>
        <h2>Pronto para agendar?</h2>
        <p>Teste no celular: cadastro, solicitação, aceite do prestador e confirmação.</p>
      </div>
      <div class="lp-cta-actions">
        <a class="lp-btn lp-btn-dark lp-btn-lg" href="<?= base_url('solicitar') ?>">Solicitar serviço</a>
        <a class="lp-btn lp-btn-ghost lp-btn-lg" href="<?= base_url('login') ?>">Entrar</a>
      </div>
    </div>
  </div>
</section>

<footer class="lp-footer">
  <div class="lp-container lp-footer-inner">
    <div>
      <strong><?= esc($appNome) ?></strong>
      <div><?= esc($appDescricao) ?></div>
    </div>
    <div class="lp-footer-links">
      <a href="<?= base_url('solicitar') ?>">Solicitar</a>
      <a href="<?= base_url('login') ?>">Entrar</a>
      <a href="<?= base_url('cadastro') ?>">Criar conta</a>
      <a href="<?= base_url('admin-acesso') ?>">Admin</a>
    </div>
  </div>
</footer>

<!-- Bottom bar mobile (web app) -->
<nav class="lp-bottom-nav" aria-label="Atalhos mobile">
  <a href="<?= base_url('/') ?>" class="on">
    <span>🏠</span><small>Início</small>
  </a>
  <a href="<?= base_url('solicitar') ?>">
    <span>➕</span><small>Solicitar</small>
  </a>
  <a href="<?= base_url('login') ?>">
    <span>👤</span><small>Entrar</small>
  </a>
  <a href="<?= base_url('admin-acesso') ?>">
    <span>⚙️</span><small>Admin</small>
  </a>
</nav>

</body>
</html>
