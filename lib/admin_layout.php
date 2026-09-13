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

/**
 * Google Fonts usados pelos packs de aparência.
 * Público: espelhar famílias novas em index.html / páginas (comentário em appearance_catalog.php).
 */
function admin_fonts_stylesheet_href(): string
{
    return 'https://fonts.googleapis.com/css2'
        . '?family=IBM+Plex+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400'
        . '&family=Space+Grotesk:wght@500;600;700'
        . '&family=Outfit:wght@500;600;700;800'
        . '&family=Sora:wght@400;500;600;700'
        . '&family=Fraunces:opsz,wght@9..144,600;9..144,700'
        . '&family=Source+Sans+3:ital,wght@0,400;0,500;0,600;0,700;1,400'
        . '&family=Plus+Jakarta+Sans:wght@500;600;700;800'
        . '&family=Manrope:wght@400;500;600;700'
        . '&family=JetBrains+Mono:wght@400;500;600'
        . '&family=Syne:wght@600;700;800'
        . '&family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400'
        . '&family=Archivo:wght@500;600;700;800'
        . '&family=Public+Sans:wght@400;500;600;700'
        . '&family=Playfair+Display:wght@600;700;800'
        . '&family=Lato:wght@400;700'
        . '&family=Nunito:wght@600;700;800'
        . '&family=Nunito+Sans:wght@400;500;600;700'
        . '&family=Barlow+Condensed:wght@600;700;800'
        . '&family=Barlow:wght@400;500;600;700'
        . '&family=Inter:wght@400;500;600;700;800'
        . '&family=Montserrat:wght@500;600;700;800'
        . '&family=Raleway:wght@500;600;700'
        . '&family=Work+Sans:wght@400;500;600;700'
        . '&family=Poppins:wght@400;500;600;700'
        . '&family=Roboto+Slab:wght@500;600;700'
        . '&family=Libre+Baskerville:wght@400;700'
        . '&family=Cormorant+Garamond:wght@500;600;700'
        . '&family=Fira+Sans:wght@400;500;600;700'
        . '&family=Figtree:wght@400;500;600;700'
        . '&family=Lexend:wght@400;500;600;700'
        . '&display=swap';
}

/**
 * Destino do botão voltar no header admin.
 * @return array{href:string,label:string}|null
 */
function admin_back_target(?array $user, ?int $leadId): ?array
{
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $isStaff = $user && function_exists('user_is_staff') && user_is_staff($user);
    $isClient = $user && function_exists('user_is_client') && user_is_client($user);
    $ownLead = ($user && function_exists('user_crm_lead_id')) ? user_crm_lead_id($user) : null;

    if ($leadId !== null) {
        if ($script === 'lead_hub.php') {
            if ($isStaff) {
                return ['href' => 'leads.php', 'label' => 'Sites de leads'];
            }
            return null;
        }
        return [
            'href' => 'lead_hub.php?lead_id=' . $leadId,
            'label' => 'Hub do lead',
        ];
    }

    if ($script === 'index.php') {
        return null;
    }

    if ($script === 'leads.php') {
        return ['href' => 'index.php', 'label' => 'Painel'];
    }

    if ($isClient && $ownLead) {
        return [
            'href' => 'lead_hub.php?lead_id=' . (int) $ownLead,
            'label' => 'Hub do lead',
        ];
    }

    return ['href' => 'index.php', 'label' => 'Painel'];
}

function admin_header(string $title, ?array $user = null): void
{
    require_once __DIR__ . '/security.php';
    require_once __DIR__ . '/auth.php';
    if (is_file(__DIR__ . '/lead_admin.php')) {
        require_once __DIR__ . '/lead_admin.php';
    }
    security_send_headers();

    $leadId = null;
    if (function_exists('lead_admin_context_id')) {
        $leadId = lead_admin_context_id();
    }
    if ($leadId === null) {
        $raw = $_GET['lead_id'] ?? null;
        if ($raw !== null && $raw !== '' && (int) $raw > 0 && $user && user_is_staff($user)) {
            $leadId = (int) $raw;
        } elseif ($raw !== null && $raw !== '' && (int) $raw > 0 && $user && user_crm_lead_id($user) === (int) $raw) {
            $leadId = (int) $raw;
        }
    }
    $leadQs = $leadId ? ('?lead_id=' . $leadId) : '';
    $isAdmin = $user && user_is_admin($user);
    $isStaff = $user && user_is_staff($user);
    $back = $user ? admin_back_target($user, $leadId) : null;
    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $showLeadCrumb = $leadId && $script !== 'lead_hub.php';
    ?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="preto" data-font="tech" data-layout="sharp">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?> — Admin</title>
  <link rel="icon" href="../favicon.svg" type="image/svg+xml">
  <link rel="icon" href="../favicon.png" type="image/png" sizes="64x64">
  <link rel="apple-touch-icon" href="../apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= h(admin_fonts_stylesheet_href()) ?>" rel="stylesheet">
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body data-page="admin">
  <div class="page">
    <header class="site-header">
      <div class="container site-header__inner">
        <?php if ($back): ?>
        <div class="admin-brand">
          <a class="admin-back" href="<?= h($back['href']) ?>" title="<?= h('Voltar · ' . $back['label']) ?>" aria-label="<?= h('Voltar · ' . $back['label']) ?>">←</a>
        </div>
        <?php endif; ?>
        <?php if ($user): ?>
        <p class="admin-user"><?= h($user['username']) ?> · <?= h((string) ($user['role'] ?? '')) ?></p>
        <nav class="site-nav" aria-label="Admin">
          <?php if ($leadId): ?>
          <a href="lead_hub.php?lead_id=<?= (int) $leadId ?>">Hub lead</a>
          <a href="contact.php<?= h($leadQs) ?>">Contato</a>
          <a href="texts.php<?= h($leadQs) ?>">Textos</a>
          <?php if (user_can_page($user, 'services')): ?>
          <a href="services.php<?= h($leadQs) ?>">Serviços</a>
          <?php endif; ?>
          <a href="images.php<?= h($leadQs) ?>">Imagens</a>
          <?php if (user_can_page($user, 'appearance')): ?>
          <a href="appearance.php<?= h($leadQs) ?>">Aparência</a>
          <?php endif; ?>
          <?php if (user_can_page($user, 'preset')): ?>
          <a href="preset.php<?= h($leadQs) ?>">Preset</a>
          <?php endif; ?>
          <?php if (user_can_page($user, 'brand')): ?>
          <a href="brand.php<?= h($leadQs) ?>">Marca</a>
          <?php endif; ?>
          <?php if (user_can_page($user, 'plan')): ?>
          <a href="plan.php<?= h($leadQs) ?>">Plano</a>
          <?php endif; ?>
          <?php if (user_can_page($user, 'sections')): ?>
          <a href="sections.php<?= h($leadQs) ?>">Visibilidade</a>
          <?php endif; ?>
          <a href="password.php">Senha</a>
          <a href="logout.php">Sair</a>
          <?php else: ?>
          <?php if (user_can_page($user, 'index')): ?><a href="index.php">Painel</a><?php endif; ?>
          <?php if (user_can_page($user, 'contact')): ?><a href="contact.php">Contato</a><?php endif; ?>
          <?php if (user_can_page($user, 'texts')): ?><a href="texts.php">Textos</a><?php endif; ?>
          <?php if ($isStaff && user_can_page($user, 'leads')): ?><a href="leads.php">Leads</a><?php endif; ?>
          <?php if (user_can_page($user, 'services')): ?><a href="services.php">Serviços</a><?php endif; ?>
          <a href="password.php">Senha</a>
          <?php if ($isAdmin): ?>
          <a href="plan.php">Plano</a>
          <a href="brand.php">Marca</a>
          <a href="sections.php">Visibilidade</a>
          <a href="backup.php">Backup</a>
          <a href="preset.php">Preset</a>
          <a href="appearance.php">Aparência</a>
          <a href="users.php">Usuários</a>
          <?php endif; ?>
          <a href="logout.php">Sair</a>
          <?php endif; ?>
        </nav>
        <?php endif; ?>
      </div>
    </header>
    <main class="container admin-main">
    <?php if ($showLeadCrumb): ?>
      <p class="eyebrow admin-crumb" style="margin:0 0 0.75rem;">
        <a href="lead_hub.php?lead_id=<?= (int) $leadId ?>">← Hub do lead #<?= (int) $leadId ?></a>
      </p>
    <?php endif; ?>
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
