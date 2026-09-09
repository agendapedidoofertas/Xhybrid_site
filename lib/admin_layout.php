<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function admin_header(string $title, ?array $user = null): void
{
    ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="preto" data-font="tech" data-layout="sharp">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?> — Xhybrid Admin</title>
  <link rel="icon" href="../favicon.svg" type="image/svg+xml">
  <link rel="icon" href="../favicon.png" type="image/png" sizes="64x64">
  <link rel="apple-touch-icon" href="../apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body data-page="admin">
  <div class="page">
    <header class="site-header">
      <div class="container site-header__inner">
        <a href="index.php" class="site-logo">X<span class="text-primary italic">hybrid</span> Admin</a>
        <?php if ($user): ?>
        <nav class="site-nav" aria-label="Admin">
          <a href="index.php">Painel</a>
          <a href="index.php#imagens">Imagens</a>
          <a href="contact.php">Contato</a>
          <a href="texts.php">Textos</a>
          <a href="services.php">Serviços</a>
          <?php if (user_is_admin($user)): ?>
          <a href="plan.php">Plano</a>
          <a href="brand.php">Marca</a>
          <a href="sections.php">Visibilidade</a>
          <a href="preset.php">Preset</a>
          <a href="appearance.php">Aparência</a>
          <a href="users.php">Usuários</a>
          <a href="password.php">Senha</a>
          <?php endif; ?>
          <a href="logout.php">Sair</a>
        </nav>
        <p class="text-muted" style="font-size:0.875rem;margin:0;"><?= h($user['username']) ?> · <?= user_is_admin($user) ? 'admin' : 'editor' ?></p>
        <?php endif; ?>
      </div>
    </header>
    <main class="container admin-main">
    <?php
}

function admin_footer(): void
{
    ?>
    </main>
  </div>
</body>
</html>
    <?php
}
