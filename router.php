<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/app_base.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) ? rawurldecode($uri) : '/';
$uri = app_strip_base_from_uri($uri);

if (preg_match('#(^|/)\.\.(/|$)#', $uri)) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Bad Request';
    exit;
}

// Bloqueia pasta data/ e bancos — exit (não return) para o php -S respeitar o 403
$dataRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'data');
$candidate = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $uri);
$realCandidate = is_file($candidate) || is_dir($candidate) ? realpath($candidate) : false;
$blockedByPath = $dataRoot && $realCandidate && str_starts_with($realCandidate, $dataRoot);
$blockedByUri = (bool) preg_match('#^/data(/|$)#i', $uri) || (bool) preg_match('#\.(sqlite3?|db)$#i', $uri);

if ($blockedByPath || $blockedByUri) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: no-store');
    echo 'Forbidden';
    exit;
}

/**
 * Lead público:
 *   Host:   {slug}.8xd.com.br ou domínio próprio (publicado em public_host)
 *   Novo:   /{slug}/{letra}{id}[/página]   ex.: /eletricistaton/x22
 *   Legado: /{slug}/{id}{letra} → 301 para o novo
 * Inativo → site-inactive.html; ausente → 404.html; ativo → HTML com assets absolutos.
 */

$servePublishedLead = null;

// Host-based routing (Basic subdomain / Medium-Pro custom) — antes do path legado
$hostHeader = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
$hostHeader = explode(':', $hostHeader)[0];
$apexHosts = ['8xd.com.br', 'www.8xd.com.br', 'localhost', '127.0.0.1'];
if ($hostHeader !== '' && !in_array($hostHeader, $apexHosts, true)) {
    try {
        require_once __DIR__ . '/lib/db.php';
        require_once __DIR__ . '/lib/published_sites.php';
        $hostRow = published_site_find_by_host(db(), $hostHeader);
        if ($hostRow) {
            $slug = strtolower((string) ($hostRow['slug'] ?? ''));
            $letter = strtolower((string) ($hostRow['url_code'] ?? ''));
            $leadId = (int) ($hostRow['crm_lead_id'] ?? 0);
            $rest = trim($uri, '/');
            // Em host dedicado, path é página (sobre.html) ou vazio
            if ($rest === '' || $rest === 'index.html') {
                $rest = '';
            }
            // Reusa o mesmo bloco injetando matches sintéticos via variáveis abaixo
            $_SERVER['XH_HOST_LEAD'] = [
                'slug' => $slug,
                'letter' => $letter,
                'leadId' => $leadId,
                'rest' => $rest,
                'row' => $hostRow,
            ];
        }
    } catch (Throwable $e) {
        // fall through to path routing
    }
}

// Legado id+letra → redirect permanente para letra+id (slug atual no banco, se existir)
if (preg_match('#^/([a-z0-9]+(?:-[a-z0-9]+)*)/(\d+)([a-z])(?:/(.*))?$#i', $uri, $mOld)) {
    $idOld = (int) $mOld[2];
    $letterOld = strtolower($mOld[3]);
    $restOld = isset($mOld[4]) ? trim($mOld[4], '/') : '';
    $slugOld = strtolower($mOld[1]);
    $targetSlug = $slugOld;
    try {
        require_once __DIR__ . '/lib/db.php';
        require_once __DIR__ . '/lib/published_sites.php';
        $rowOld = published_site_get_by_lead(db(), $idOld);
        if ($rowOld && strtolower((string) ($rowOld['url_code'] ?? '')) === $letterOld) {
            $s = trim((string) ($rowOld['slug'] ?? ''));
            if ($s !== '') {
                $targetSlug = strtolower($s);
            }
        }
    } catch (Throwable $e) {
        // mantém slug da URL antiga
    }
    $target = app_path('/' . $targetSlug . '/' . $letterOld . $idOld);
    if ($restOld !== '') {
        $target = rtrim($target, '/') . '/' . $restOld;
    }
    header('Location: ' . $target, true, 301);
    exit;
}

$hostLead = $_SERVER['XH_HOST_LEAD'] ?? null;
$pathLeadMatch = preg_match('#^/([a-z0-9]+(?:-[a-z0-9]+)*)/([a-z])(\d+)(?:/(.*))?$#i', $uri, $m);

if (is_array($hostLead) || $pathLeadMatch) {
    require_once __DIR__ . '/lib/db.php';
    require_once __DIR__ . '/lib/published_sites.php';
    require_once __DIR__ . '/lib/security.php';
    security_send_headers();

    if (is_array($hostLead)) {
        $slug = strtolower((string) ($hostLead['slug'] ?? ''));
        $letter = strtolower((string) ($hostLead['letter'] ?? ''));
        $leadId = (int) ($hostLead['leadId'] ?? 0);
        $rest = (string) ($hostLead['rest'] ?? '');
        $row = is_array($hostLead['row'] ?? null) ? $hostLead['row'] : null;
    } else {
        $slug = strtolower($m[1]);
        $letter = strtolower($m[2]);
        $leadId = (int) $m[3];
        $rest = isset($m[4]) ? trim($m[4], '/') : '';
        try {
            $row = published_site_find_public(db(), $slug, $leadId, $letter);
        } catch (Throwable $e) {
            $row = null;
        }
    }

    $serveHtmlFile = static function (string $file, int $code = 404) use ($slug, $leadId, $letter): void {
        http_response_code($code);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        $path = __DIR__ . '/' . $file;
        if (!is_file($path)) {
            echo 'Not Found';
            exit;
        }
        $html = file_get_contents($path);
        if ($html === false) {
            echo 'Not Found';
            exit;
        }
        $html = published_site_absolutize_html($html);
        echo $html;
        exit;
    };

    $serveInactive = static function (array $row) use ($serveHtmlFile): void {
        require_once __DIR__ . '/lib/payment_grace.php';
        $ctx = payment_inactive_context(db(), $row);
        $json = json_encode($ctx, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        $path = __DIR__ . '/site-inactive.html';
        $html = file_get_contents($path);
        if ($html === false) {
            echo 'Unavailable';
            exit;
        }
        $html = published_site_absolutize_html($html);
        $html = preg_replace(
            '#<script type="application/json" id="xh-inactive-ctx">.*?</script>#s',
            '<script type="application/json" id="xh-inactive-ctx">' . $json . '</script>',
            $html,
            1
        ) ?? $html;
        echo $html;
        exit;
    };

    if (!$row) {
        $serveHtmlFile('404.html', 404);
    }

    // Grace 72h: se pending_confirm expirou, desativa antes de decidir a página
    require_once __DIR__ . '/lib/payment_grace.php';
    payment_expire_if_needed(db(), $leadId);
    try {
        if (is_array($hostLead)) {
            $row = published_site_find_by_host(db(), $hostHeader) ?? $row;
        } else {
            $row = published_site_find_public(db(), $slug, $leadId, $letter) ?? $row;
        }
    } catch (Throwable $e) {
        // keep $row
    }

    if ((int) ($row['site_active'] ?? 0) !== 1) {
        $serveInactive($row);
    }

    $page = 'index.html';
    if ($rest !== '') {
        $baseName = basename($rest);
        if (preg_match('/^[a-z0-9_-]+\.html$/i', $baseName) && is_file(__DIR__ . '/' . $baseName)) {
            $page = $baseName;
        } elseif ($rest !== '' && $rest !== 'index.html') {
            $serveHtmlFile('404.html', 404);
        }
    }

    $htmlPath = __DIR__ . DIRECTORY_SEPARATOR . $page;
    $html = file_get_contents($htmlPath);
    if ($html === false) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Failed to load page';
        exit;
    }

    // Settings do lead no HTML (evita flash de textos da agência: "Projetos", etc.)
    $leadSettings = published_site_settings_overlay($row);
    $leadBoot = '<style id="lead-boot-style">html.lead-booting body{visibility:hidden!important}</style>'
        . '<script>'
        . 'document.documentElement.classList.add("lead-booting");'
        . 'window.__xhybridAppBase=' . json_encode(app_base_path(), JSON_UNESCAPED_SLASHES) . ';'
        . 'window.__xhybridLeadPath=' . json_encode([
            'slug' => $slug,
            'leadId' => $leadId,
            'code' => $letter,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) . ';'
        . 'window.__xhybridLeadSettings=' . json_encode(
            $leadSettings,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) . ';'
        . 'setTimeout(function(){document.documentElement.classList.remove("lead-booting");},4000);'
        . '</script>';
    $html = preg_replace('/<head([^>]*)>/i', '<head$1>' . $leadBoot, $html, 1) ?? ($leadBoot . $html);
    $html = published_site_absolutize_html($html);

    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store');
    echo $html;
    exit;
}

$path = __DIR__ . $uri;

if (is_dir($path)) {
    foreach (['index.php', 'index.html'] as $index) {
        if (is_file($path . DIRECTORY_SEPARATOR . $index)) {
            header('Location: ' . rtrim(app_path($uri), '/') . '/' . $index);
            exit;
        }
    }
}

if ($uri !== '/' && is_file($path)) {
    return false;
}

if ($uri === '/' || $uri === '') {
    header('Location: ' . app_path('/index.html'));
    exit;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
$notFound = __DIR__ . '/404.html';
if (is_file($notFound)) {
    require_once __DIR__ . '/lib/published_sites.php';
    $html = file_get_contents($notFound);
    echo $html !== false ? published_site_absolutize_html($html) : 'Not Found';
} else {
    echo 'Not Found';
}
exit;
