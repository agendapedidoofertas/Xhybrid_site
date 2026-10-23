<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/security.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();

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

$state = (string) ($_GET['state'] ?? '');
$code = (string) ($_GET['code'] ?? '');
$storedState = (string) ($_SESSION['google_oauth_state'] ?? '');
unset($_SESSION['google_oauth_state']);

if ($state === '' || $state !== $storedState) {
    http_response_code(403);
    admin_header('Erro de Autenticação');
    echo '<div class="admin-flash admin-flash--error" style="max-width:32rem;margin:3rem auto;">Falha na verificação de segurança (CSRF token inválido). Tente novamente.</div>';
    admin_footer();
    exit;
}

if ($code === '') {
    header('Location: login.php?error=google_cancelled');
    exit;
}

$cfg = google_oauth_config();
$scheme = request_is_https() ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$redirectUri = $scheme . '://' . $host . '/admin/google_callback.php';

// Troca code por token
$tokenPost = http_build_query([
    'code' => $code,
    'client_id' => $cfg['client_id'],
    'client_secret' => $cfg['client_secret'],
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code',
]);

$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $tokenPost,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 10,
]);
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode((string) $tokenResponse, true);
$accessToken = (string) ($tokenData['access_token'] ?? '');

if ($accessToken === '') {
    header('Location: login.php?error=google_token_failed');
    exit;
}

// Busca dados do perfil
$ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_TIMEOUT => 10,
]);
$userResponse = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode((string) $userResponse, true);
$googleEmail = strtolower(trim((string) ($userInfo['email'] ?? '')));
$googleSub = (string) ($userInfo['sub'] ?? '');
$emailVerified = (bool) ($userInfo['email_verified'] ?? false);

if (!$emailVerified || $googleEmail === '') {
    header('Location: login.php?error=google_unverified');
    exit;
}

// Regra de segurança fundamental: Allowlist exclusiva de administradores cadastrados
$pdo = db();
$stmt = $pdo->prepare('SELECT id, username, role, email, google_id FROM users WHERE LOWER(email) = :email OR google_id = :gid');
$stmt->execute([':email' => $googleEmail, ':gid' => $googleSub]);
$user = $stmt->fetch();

if (!$user || ($user['role'] ?? '') !== 'admin') {
    http_response_code(403);
    admin_header('Acesso Negado');
    echo '<div style="max-width:32rem;margin:3rem auto;padding:1.5rem;background:#18181b;border:1px solid #ef4444;border-radius:0.75rem;text-align:center;">';
    echo '<h2 style="color:#ef4444;margin:0 0 1rem;">Acesso Não Autorizado</h2>';
    echo '<p style="color:#a1a1aa;margin:0 0 1.5rem;">A conta Google <strong>' . htmlspecialchars($googleEmail, ENT_QUOTES, 'UTF-8') . '</strong> não possui permissão de administrador no sistema.</p>';
    echo '<a href="login.php" class="btn btn-primary" style="display:inline-block;">Voltar para o Login</a>';
    echo '</div>';
    admin_footer();
    exit;
}

// Vincula google_id se ainda não estiver salvo
if (empty($user['google_id']) && $googleSub !== '') {
    $upd = $pdo->prepare('UPDATE users SET google_id = :gid WHERE id = :id');
    $upd->execute([':gid' => $googleSub, ':id' => (int) $user['id']]);
}

login_user_by_id((int) $user['id']);
header('Location: index.php');
exit;
