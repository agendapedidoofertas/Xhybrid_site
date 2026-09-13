<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * URL de mídia relativa ao site → caminho usable a partir de /admin/.
 * URLs absolutas (http/https/data) permanecem iguais.
 */
function admin_media_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return admin_placeholder_src();
    }
    if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, 'data:')) {
        return $url;
    }
    return '../' . ltrim(str_replace('\\', '/', $url), '/');
}

/** SVG do robô placeholder (ASCII limpo). */
function admin_robot_svg(): string
{
    static $svg = null;
    if (is_string($svg)) {
        return $svg;
    }
    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'placeholder-robot.svg';
    if (is_file($path)) {
        $raw = file_get_contents($path);
        if (is_string($raw) && str_contains($raw, '<svg')) {
            $svg = $raw;
            return $svg;
        }
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 132"><rect width="120" height="132" rx="10" fill="#0a0a0a"/><circle cx="60" cy="15" r="4" fill="#c8c8c8"/><rect x="34" y="30" width="52" height="36" rx="10" fill="#1a1a1a" stroke="#fff" stroke-opacity=".2"/><rect x="47" y="44" width="8" height="8" rx="2" fill="#e8e8e8"/><rect x="65" y="44" width="8" height="8" rx="2" fill="#e8e8e8"/><rect x="38" y="72" width="44" height="32" rx="8" fill="#151515"/><text x="60" y="94" text-anchor="middle" fill="#fff" font-size="14" font-family="sans-serif" font-weight="700">X</text></svg>';
    return $svg;
}

/** Data-URI do robô (para <img src> / onerror). */
function admin_placeholder_src(): string
{
    static $uri = null;
    if (is_string($uri)) {
        return $uri;
    }
    $uri = 'data:image/svg+xml;charset=utf-8,' . rawurlencode(admin_robot_svg());
    return $uri;
}

/** Thumb HTML: imagem real ou robô inline (nunca ícone quebrado). */
function admin_thumb_html(string $url, bool $showReal): string
{
    $ph = admin_placeholder_src();
    if (!$showReal) {
        return '<span class="admin-thumb admin-thumb--robot" aria-hidden="true">' . admin_robot_svg() . '</span>';
    }
    $src = admin_media_url($url);
    return '<img class="admin-thumb" src="' . h($src) . '" alt="" width="56" height="56" referrerpolicy="no-referrer"'
        . ' data-placeholder="' . h($ph) . '"'
        . ' onerror="if(!this.dataset.ph){this.dataset.ph=\'1\';this.src=this.dataset.placeholder;this.classList.add(\'is-placeholder\');}">';
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
        <a href="index.php" class="site-logo">X<span class="text-primary italic">hybrid</span> <span style="font-style:normal;font-weight:600;opacity:0.7">Admin</span></a>
        <?php if ($user): ?>
        <p class="admin-user"><?= h($user['username']) ?> · <?= user_is_admin($user) ? 'admin' : 'editor' ?></p>
        <nav class="site-nav" aria-label="Admin">
          <a href="index.php">Painel</a>
          <a href="index.php#imagens">Imagens</a>
          <a href="contact.php">Contato</a>
          <a href="texts.php">Textos</a>
          <a href="leads.php">Leads</a>
          <a href="services.php">Serviços</a>
          <a href="password.php">Senha</a>
          <?php if (user_is_admin($user)): ?>
          <a href="plan.php">Plano</a>
          <a href="brand.php">Marca</a>
          <a href="sections.php">Visibilidade</a>
          <a href="backup.php">Backup</a>
          <a href="preset.php">Preset</a>
          <a href="appearance.php">Aparência</a>
          <a href="users.php">Usuários</a>
          <?php endif; ?>
          <a href="logout.php">Sair</a>
        </nav>
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
