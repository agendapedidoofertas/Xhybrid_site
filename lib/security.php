<?php

declare(strict_types=1);

/**
 * Aceita apenas http(s). Rejeita javascript:, data:, etc.
 */
function url_http_only(string $value): string
{
    $value = trim(str_replace(['<', '>', '"', "'"], '', $value));
    if ($value === '') {
        return '';
    }
    if (!preg_match('#^https?://#i', $value)) {
        // Domínio sem esquema → https
        if (preg_match('#^[a-z0-9][a-z0-9\-./?#=&%+_]*$#i', $value) && !preg_match('#^(javascript|data|vbscript|file):#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        } else {
            return '';
        }
    }
    if (preg_match('#^(javascript|data|vbscript|file):#i', $value)) {
        return '';
    }
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
        return '';
    }
    $parts = parse_url($value);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    if ($scheme !== 'http' && $scheme !== 'https') {
        return '';
    }
    return $value;
}

function request_is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
        return true;
    }
    $fwd = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    return $fwd === 'https';
}

/** Headers de segurança básicos (idempotente por request). */
function security_send_headers(): void
{
    static $done = false;
    if ($done || headers_sent()) {
        return;
    }
    $done = true;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header(
        "Content-Security-Policy: default-src 'self'; "
        . "img-src 'self' data: https: blob:; "
        . "media-src 'self' https: blob:; "
        . "font-src 'self' https://fonts.gstatic.com data:; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
        . "script-src 'self' 'unsafe-inline' https://www.googletagmanager.com https://connect.facebook.net; "
        . "connect-src 'self' https:; "
        . "frame-ancestors 'self'; "
        . "base-uri 'self'; "
        . "form-action 'self' https://wa.me https://api.whatsapp.com"
    );
}

function api_json_error(string $publicMessage, Throwable $e, int $code = 500): void
{
    error_log('[xhybrid] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(['error' => $publicMessage], JSON_UNESCAPED_UNICODE);
}
