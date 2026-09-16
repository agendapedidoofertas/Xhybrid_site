<?php

declare(strict_types=1);

/**
 * Prefixo de URL quando o site NÃO está na raiz do domínio.
 * Local (php -S na pasta do projeto): '' — sem arquivo de config.
 * Produção em subpasta: data/app_base_path.php → '/xhybrid_site'
 */

function app_base_path(): string
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $raw = '';
    $env = getenv('XHYBRID_APP_BASE');
    if (is_string($env) && trim($env) !== '') {
        $raw = trim($env);
    } else {
        $cfg = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'app_base_path.php';
        if (is_file($cfg)) {
            $v = include $cfg;
            if (is_string($v) && trim($v) !== '') {
                $raw = trim($v);
            }
        }
    }

    $raw = str_replace('\\', '/', $raw);
    $raw = '/' . trim($raw, '/');
    if ($raw === '/' || $raw === '/.' || $raw === '') {
        $cached = '';
        return $cached;
    }

    $cached = $raw;
    return $cached;
}

/** Junta base + path absoluto do app (ex.: /slug/x22 → /xhybrid_site/slug/x22). */
function app_path(string $path): string
{
    $path = '/' . ltrim(str_replace('\\', '/', $path), '/');
    if ($path === '/') {
        $base = app_base_path();
        return $base === '' ? '/' : $base . '/';
    }
    $base = app_base_path();
    return $base === '' ? $path : $base . $path;
}

/** Remove o prefixo de app da REQUEST_URI para o router tratar como raiz. */
function app_strip_base_from_uri(string $uri): string
{
    $uri = '/' . ltrim(str_replace('\\', '/', $uri), '/');
    if ($uri !== '/' && str_ends_with($uri, '/')) {
        // keep trailing only for root; lead paths usually without
    }
    $base = app_base_path();
    if ($base === '') {
        return $uri === '' ? '/' : $uri;
    }
    if ($uri === $base || $uri === $base . '/') {
        return '/';
    }
    if (str_starts_with($uri, $base . '/')) {
        $rest = substr($uri, strlen($base));
        return $rest === '' ? '/' : $rest;
    }
    return $uri === '' ? '/' : $uri;
}
