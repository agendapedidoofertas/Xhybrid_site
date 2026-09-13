<?php

declare(strict_types=1);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = is_string($uri) ? rawurldecode($uri) : '/';

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
 * Lead público: /{slug}/{leadId}{letra}[/página]
 * Ex.: /eletricista-silva/15f  ou  /eletricista-silva/15f/sobre.html
 * Inativo → site-inactive.html; ausente → 404.html; ativo → HTML com assets absolutos.
 */
if (preg_match('#^/([a-z0-9]+(?:-[a-z0-9]+)*)/(\d+)([a-z])(?:/(.*))?$#i', $uri, $m)) {
    require_once __DIR__ . '/lib/db.php';
    require_once __DIR__ . '/lib/published_sites.php';
    require_once __DIR__ . '/lib/security.php';
    security_send_headers();

    $slug = strtolower($m[1]);
    $leadId = (int) $m[2];
    $letter = strtolower($m[3]);
    $rest = isset($m[4]) ? trim($m[4], '/') : '';

    try {
        $row = published_site_find_public(db(), $slug, $leadId, $letter);
    } catch (Throwable $e) {
        $row = null;
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

    if (!$row) {
        $serveHtmlFile('404.html', 404);
    }

    if ((int) ($row['site_active'] ?? 0) !== 1) {
        $serveHtmlFile('site-inactive.html', 403);
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
            header('Location: ' . rtrim($uri, '/') . '/' . $index);
            exit;
        }
    }
}

if ($uri !== '/' && is_file($path)) {
    return false;
}

if ($uri === '/' || $uri === '') {
    header('Location: /index.html');
    exit;
}

http_response_code(404);
header('Content-Type: text/html; charset=utf-8');
$notFound = __DIR__ . '/404.html';
if (is_file($notFound)) {
    readfile($notFound);
} else {
    echo 'Not Found';
}
exit;
