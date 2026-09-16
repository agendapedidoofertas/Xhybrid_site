<?php

declare(strict_types=1);

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Paths internos dos ícones do admin (painel + menu).
 */
function admin_icon_paths(string $key): string
{
    return match ($key) {
        'contact' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.81.36 1.6.68 2.34a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.74.32 1.53.55 2.34.68A2 2 0 0 1 22 16.92z"/>',
        'texts' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/>',
        'images' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
        'services' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'appearance' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>',
        'preset' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3z"/><path d="M19 15l.9 2.6L22.5 18.5l-2.6.9L19 22l-.9-2.6L15.5 18.5l2.6-.9L19 15z"/>',
        'brand' => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><circle cx="7" cy="7" r="1.5"/>',
        'plan' => '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
        'sections' => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'leads' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'backup' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/>',
        'password' => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'lead_site' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
        'painel' => '<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
        'hub' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
        'logout' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        default => '<circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
    };
}

/** Ícone SVG do card do painel / hub. */
function admin_hub_icon(string $key): string
{
    $common = ' class="admin-hub-icon__svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
    return '<span class="admin-hub-icon" aria-hidden="true"><svg' . $common . '>' . admin_icon_paths($key) . '</svg></span>';
}

/** Ícone compacto para o menu superior. */
function admin_nav_icon(string $key): string
{
    $common = ' class="admin-nav-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
    return '<svg' . $common . '>' . admin_icon_paths($key) . '</svg>';
}

/** Ordena itens por rótulo (pt-BR aproximado). */
function admin_sort_by_label(array $items): array
{
    usort($items, static function (array $a, array $b): int {
        $la = (string) ($a['label'] ?? '');
        $lb = (string) ($b['label'] ?? '');
        if (function_exists('iconv')) {
            $na = (string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $la);
            $nb = (string) @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $lb);
            if ($na !== '' && $nb !== '') {
                $la = $na;
                $lb = $nb;
            }
        }
        return strcasecmp($la, $lb);
    });
    return $items;
}

/**
 * Cards do painel da agência (ordem alfabética).
 *
 * @return list<array{href:string,icon:string,label:string,desc:string,show:bool}>
 */
function admin_agency_hub_links(array $user): array
{
    $items = [
        ['href' => 'appearance.php', 'icon' => 'appearance', 'label' => 'Aparência', 'desc' => 'Tema, fonte e layout', 'show' => user_can_page($user, 'appearance')],
        ['href' => 'backup.php', 'icon' => 'backup', 'label' => 'Backup', 'desc' => 'Exportar e restaurar', 'show' => user_is_admin($user)],
        ['href' => 'contact.php', 'icon' => 'contact', 'label' => 'Contato', 'desc' => 'Editar contato da vitrine', 'show' => user_can_page($user, 'contact')],
        ['href' => 'images.php', 'icon' => 'images', 'label' => 'Imagens', 'desc' => 'Logo, favicon, hero e about', 'show' => user_can_page($user, 'images')],
        ['href' => 'leads.php', 'icon' => 'leads', 'label' => 'Leads', 'desc' => 'Sites publicados do CRM', 'show' => user_is_staff($user) && user_can_page($user, 'leads')],
        ['href' => 'brand.php', 'icon' => 'brand', 'label' => 'Marca', 'desc' => 'Nome e identidade', 'show' => user_can_page($user, 'brand')],
        ['href' => 'plan.php', 'icon' => 'plan', 'label' => 'Plano', 'desc' => 'Plano e recursos', 'show' => user_can_page($user, 'plan')],
        ['href' => 'preset.php', 'icon' => 'preset', 'label' => 'Preset', 'desc' => 'Aplicar preset de nicho', 'show' => user_can_page($user, 'preset')],
        ['href' => 'password.php', 'icon' => 'password', 'label' => 'Senha', 'desc' => 'Alterar sua senha', 'show' => true],
        ['href' => 'services.php', 'icon' => 'services', 'label' => 'Serviços', 'desc' => 'Catálogo de serviços', 'show' => user_can_page($user, 'services')],
        ['href' => 'texts.php', 'icon' => 'texts', 'label' => 'Textos', 'desc' => 'Editar textos da vitrine', 'show' => user_can_page($user, 'texts')],
        ['href' => 'users.php', 'icon' => 'users', 'label' => 'Usuários', 'desc' => 'Contas e permissões', 'show' => user_is_admin($user)],
        ['href' => 'account.php', 'icon' => 'password', 'label' => 'Minha assinatura', 'desc' => 'Plano e pagamento', 'show' => user_is_client($user)],
        ['href' => 'sections.php', 'icon' => 'sections', 'label' => 'Visibilidade', 'desc' => 'Seções do site', 'show' => user_can_page($user, 'sections')],
    ];
    return admin_sort_by_label($items);
}

/**
 * Cards do hub de um lead (ordem alfabética).
 *
 * @return list<array{href:string,icon:string,label:string,show:bool}>
 */
function admin_lead_hub_links(array $user, string $planTier = 'basic'): array
{
    require_once __DIR__ . '/plans.php';
    $planTier = plan_normalize($planTier);
    $showBrand = user_can_page($user, 'brand')
        && (user_is_staff($user) || plan_allows_brand_edit($planTier));
    $items = [
        ['href' => 'appearance.php', 'icon' => 'appearance', 'label' => 'Aparência', 'show' => user_can_page($user, 'appearance')],
        ['href' => 'contact.php', 'icon' => 'contact', 'label' => 'Contato', 'show' => user_can_page($user, 'contact') || user_is_staff($user)],
        ['href' => 'images.php', 'icon' => 'images', 'label' => 'Imagens', 'show' => user_is_staff($user) || user_can_page($user, 'images')],
        ['href' => 'brand.php', 'icon' => 'brand', 'label' => 'Marca', 'show' => $showBrand],
        ['href' => 'plan.php', 'icon' => 'plan', 'label' => 'Plano', 'show' => user_can_page($user, 'plan')],
        ['href' => 'preset.php', 'icon' => 'preset', 'label' => 'Preset', 'show' => user_can_page($user, 'preset')],
        ['href' => 'lead_site.php', 'icon' => 'lead_site', 'label' => 'Resumo rápido', 'show' => user_can_page($user, 'lead_site') || user_is_staff($user)],
        ['href' => 'services.php', 'icon' => 'services', 'label' => 'Serviços', 'show' => user_can_page($user, 'services')],
        ['href' => 'texts.php', 'icon' => 'texts', 'label' => 'Textos', 'show' => user_can_page($user, 'texts') || user_is_staff($user)],
        ['href' => 'sections.php', 'icon' => 'sections', 'label' => 'Visibilidade', 'show' => user_can_page($user, 'sections')],
    ];
    return admin_sort_by_label($items);
}

/**
 * Itens do menu superior (ordem alfabética; Sair por último).
 *
 * @return list<array{href:string,icon:string,label:string}>
 */
function admin_nav_items(array $user, ?int $leadId): array
{
    $qs = $leadId ? ('?lead_id=' . $leadId) : '';
    $items = [];

    if ($leadId) {
        $planTier = 'basic';
        if (is_file(__DIR__ . '/published_sites.php') && is_file(__DIR__ . '/db.php')) {
            require_once __DIR__ . '/db.php';
            require_once __DIR__ . '/published_sites.php';
            require_once __DIR__ . '/plans.php';
            try {
                $ps = published_site_get_by_lead(db(), $leadId);
                if (is_array($ps)) {
                    $planTier = plan_normalize((string) ($ps['plan_tier'] ?? 'basic'));
                }
            } catch (Throwable $e) {
                // menu sem plano
            }
        }
        $items[] = ['href' => 'lead_hub.php?lead_id=' . $leadId, 'icon' => 'hub', 'label' => 'Hub lead'];
        foreach (admin_lead_hub_links($user, $planTier) as $row) {
            if (!$row['show'] || ($row['href'] ?? '') === 'lead_site.php') {
                continue;
            }
            $items[] = [
                'href' => $row['href'] . $qs,
                'icon' => $row['icon'],
                'label' => $row['label'],
            ];
        }
        $items[] = ['href' => 'password.php', 'icon' => 'password', 'label' => 'Senha'];
    } else {
        if (user_can_page($user, 'index')) {
            $items[] = ['href' => 'index.php', 'icon' => 'painel', 'label' => 'Painel'];
        }
        foreach (admin_agency_hub_links($user) as $row) {
            if (!$row['show']) {
                continue;
            }
            $items[] = [
                'href' => $row['href'],
                'icon' => $row['icon'],
                'label' => $row['label'],
            ];
        }
    }

    $items = admin_sort_by_label($items);
    $items[] = ['href' => 'logout.php', 'icon' => 'logout', 'label' => 'Sair'];
    return $items;
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

/** Cabeça SVG do Roboto (inline na bolha/painel). */
function admin_roboto_head_svg(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $path = dirname(__DIR__) . '/assets/roboto-head.svg';
    if (!is_file($path)) {
        $cached = '';
        return $cached;
    }
    $svg = (string) file_get_contents($path);
    $svg = preg_replace('/<\?xml[^>]*\?>\s*/', '', $svg) ?? $svg;
    $cached = trim($svg);
    return $cached;
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
          <?php foreach (admin_nav_items($user, $leadId) as $navItem): ?>
          <a href="<?= h($navItem['href']) ?>"><?= admin_nav_icon($navItem['icon']) ?><span><?= h($navItem['label']) ?></span></a>
          <?php endforeach; ?>
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
    require_once __DIR__ . '/auth.php';
    $user = current_user();
    $leadId = null;
    if ($user) {
        if (is_file(__DIR__ . '/lead_admin.php')) {
            require_once __DIR__ . '/lead_admin.php';
        }
        if (function_exists('lead_admin_context_id')) {
            $leadId = lead_admin_context_id();
        }
        if ($leadId === null) {
            $raw = $_GET['lead_id'] ?? null;
            if ($raw !== null && $raw !== '' && (int) $raw > 0) {
                $candidate = (int) $raw;
                if (user_is_staff($user) || user_crm_lead_id($user) === $candidate) {
                    $leadId = $candidate;
                }
            }
        }
    }
    $headSvg = $user ? admin_roboto_head_svg() : '';
    $payloadJson = '';
    if ($user) {
        require_once __DIR__ . '/roboto.php';
        $payloadJson = json_encode(
            roboto_payload($user, $leadId),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
        );
        if ($payloadJson === false) {
            $payloadJson = '{"context":{},"chips":[]}';
        }
    }
    ?>
    </main>
  </div>
  <?php if ($user && $headSvg !== ''): ?>
  <button type="button" class="roboto-fab" id="roboto-fab" aria-expanded="false" aria-controls="roboto-panel" title="Roboto — ajuda" aria-label="Abrir Roboto, ajuda do painel">
    <?= $headSvg ?>
  </button>
  <div class="roboto-panel" id="roboto-panel" role="dialog" aria-labelledby="roboto-panel-title" hidden>
    <div class="roboto-panel__head">
      <div class="roboto-panel__head-icon" id="roboto-head-icon" aria-hidden="true"><?= $headSvg ?></div>
      <div>
        <p class="roboto-panel__title" id="roboto-panel-title">Roboto</p>
        <p class="roboto-panel__sub">Conversa de ajuda do painel</p>
      </div>
      <button type="button" class="roboto-panel__close" id="roboto-close" aria-label="Fechar">×</button>
    </div>
    <div class="roboto-thread" id="roboto-thread" aria-live="polite"></div>
    <div class="roboto-composer">
      <button type="button" class="roboto-composer__btn" id="roboto-topics-open" aria-expanded="false" aria-controls="roboto-drawer">
        Escolher tópico
      </button>
    </div>
    <div class="roboto-drawer" id="roboto-drawer" hidden>
      <div class="roboto-drawer__bar">
        <p class="roboto-drawer__title">Tópicos</p>
        <button type="button" class="roboto-drawer__close" id="roboto-drawer-close" aria-label="Fechar tópicos">×</button>
      </div>
      <div class="roboto-panel__filter">
        <label class="visually-hidden" for="roboto-filter">Filtrar tópicos</label>
        <input type="search" id="roboto-filter" placeholder="Filtrar tópicos…" autocomplete="off">
      </div>
      <div class="roboto-panel__topics" id="roboto-groups"></div>
    </div>
  </div>
  <script type="application/json" id="roboto-data"><?= $payloadJson ?></script>
  <script src="js/roboto.js" defer></script>
  <?php endif; ?>
</body>
</html>
    <?php
}
