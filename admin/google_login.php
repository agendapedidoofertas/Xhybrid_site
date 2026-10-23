<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/security.php';

auth_boot_session();

// Carrega chaves do Google (se existirem)
function google_oauth_config(): array
{
    $configFile = dirname(__DIR__) . '/data/google_auth.php';
    $cfg = [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
    ];
    if (is_file($configFile)) {
        $loaded = include $configFile;
        if (is_array($loaded)) {
            $cfg['client_id'] = (string) ($loaded['client_id'] ?? $cfg['client_id']);
            $cfg['client_secret'] = (string) ($loaded['client_secret'] ?? $cfg['client_secret']);
        }
    }
    return $cfg;
}

$cfg = google_oauth_config();
if (empty($cfg['client_id']) || empty($cfg['client_secret'])) {
    header('Location: login.php?error=google_not_configured');
    exit;
}

// Gera token de estado anti-CSRF
$state = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;

// Detecta URL de callback
$scheme = request_is_https() ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$redirectUri = $scheme . '://' . $host . '/admin/google_callback.php';

$params = [
    'client_id' => $cfg['client_id'],
    'redirect_uri' => $redirectUri,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account',
];

$authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
header('Location: ' . $authUrl);
exit;
