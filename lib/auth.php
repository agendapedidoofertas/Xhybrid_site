<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/security.php';

function auth_boot_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = request_is_https();

    session_name('xhybrid_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function user_count(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function admin_count(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
}

function current_user(): ?array
{
    $id = $_SESSION['user_id'] ?? null;
    if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, username, role, crm_lead_id, created_at FROM users WHERE id = :id');
    $stmt->execute([':id' => (int) $id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function user_is_admin(?array $user): bool
{
    return $user !== null && ($user['role'] ?? '') === 'admin';
}

function user_is_staff(?array $user = null): bool
{
    $user = $user ?? current_user();
    if ($user === null) {
        return false;
    }
    $role = (string) ($user['role'] ?? '');
    return $role === 'admin' || $role === 'editor';
}

function user_crm_lead_id(?array $user = null): ?int
{
    $user = $user ?? current_user();
    if ($user === null) {
        return null;
    }
    $id = $user['crm_lead_id'] ?? null;
    if ($id === null || $id === '') {
        return null;
    }
    $n = (int) $id;
    return $n > 0 ? $n : null;
}

function user_is_client(?array $user = null): bool
{
    $user = $user ?? current_user();
    if ($user === null) {
        return false;
    }
    $role = (string) ($user['role'] ?? '');
    return $role === 'client_medium' || $role === 'client_pro';
}

/**
 * Páginas permitidas por papel (para clients e filtros de nav).
 *
 * @return list<string>
 */
function user_allowed_pages(?array $user = null): array
{
    $user = $user ?? current_user();
    if ($user === null) {
        return [];
    }
    $role = (string) ($user['role'] ?? '');
    if ($role === 'admin') {
        return [
            'index', 'contact', 'texts', 'leads', 'lead_hub', 'lead_site', 'services',
            'password', 'plan', 'brand', 'sections', 'backup', 'preset', 'appearance', 'users',
        ];
    }
    if ($role === 'editor') {
        return [
            'index', 'contact', 'texts', 'leads', 'lead_hub', 'lead_site', 'services', 'password',
        ];
    }
    if ($role === 'client_pro') {
        return [
            'index', 'contact', 'texts', 'lead_hub', 'lead_site', 'services',
            'password', 'plan', 'brand', 'sections', 'preset', 'appearance',
        ];
    }
    if ($role === 'client_medium') {
        return [
            'index', 'contact', 'texts', 'lead_hub', 'lead_site', 'password',
        ];
    }
    return ['password'];
}

function user_can_page(?array $user, string $page): bool
{
    return in_array($page, user_allowed_pages($user), true);
}

/**
 * Staff: qualquer lead. Client: só o próprio crm_lead_id.
 */
function require_lead_access(int $leadId): array
{
    $user = require_admin();
    if ($leadId <= 0) {
        http_response_code(404);
        exit;
    }
    if (user_is_staff($user)) {
        return $user;
    }
    $own = user_crm_lead_id($user);
    if ($own !== null && $own === $leadId) {
        return $user;
    }
    http_response_code(403);
    header('Location: index.php');
    exit;
}

/** Qualquer usuário logado. */
function require_admin(): array
{
    $user = current_user();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

/** Somente role admin (gestão de usuários / fábrica). */
function require_role_admin(): array
{
    $user = require_admin();
    if (!user_is_admin($user)) {
        header('Location: index.php');
        exit;
    }
    return $user;
}

/** Exige página na allowlist do papel. */
function require_page(string $page): array
{
    $user = require_admin();
    if (!user_can_page($user, $page)) {
        $lead = user_crm_lead_id($user);
        if ($lead !== null && user_can_page($user, 'lead_hub')) {
            header('Location: lead_hub.php?lead_id=' . $lead);
        } else {
            header('Location: index.php');
        }
        exit;
    }
    return $user;
}

function login_user(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    return true;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'] ?? '/',
            'domain' => $params['domain'] ?? '',
            'secure' => (bool) ($params['secure'] ?? false),
            'httponly' => (bool) ($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }
    session_destroy();
}

/**
 * @param string $role admin|editor|client_medium|client_pro
 */
function create_user(string $username, string $password, string $role = 'admin', ?int $crmLeadId = null): void
{
    $allowed = ['admin', 'editor', 'client_medium', 'client_pro'];
    if (!in_array($role, $allowed, true)) {
        $role = 'editor';
    }
    if ($role === 'client_medium' || $role === 'client_pro') {
        if ($crmLeadId === null || $crmLeadId <= 0) {
            throw new InvalidArgumentException('Cliente precisa de crm_lead_id.');
        }
    } else {
        $crmLeadId = null;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO users (username, password_hash, role, crm_lead_id, created_at)
         VALUES (:username, :password_hash, :role, :crm_lead_id, :created_at)'
    );
    $stmt->execute([
        ':username' => $username,
        ':password_hash' => $hash,
        ':role' => $role,
        ':crm_lead_id' => $crmLeadId,
        ':created_at' => gmdate('c'),
    ]);
}

function change_password(int $userId, string $current, string $next): string
{
    $stmt = db()->prepare('SELECT password_hash FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();
    if (!$row || !password_verify($current, $row['password_hash'])) {
        return 'Senha atual incorreta.';
    }
    $update = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $update->execute([
        ':hash' => password_hash($next, PASSWORD_DEFAULT),
        ':id' => $userId,
    ]);
    return '';
}

/** Admin redefine senha de outro usuário (não exige senha atual). */
function admin_reset_password(int $userId, string $next): string
{
    if (strlen($next) < 8) {
        return 'A nova senha precisa ter pelo menos 8 caracteres.';
    }
    $stmt = db()->prepare('SELECT id FROM users WHERE id = :id');
    $stmt->execute([':id' => $userId]);
    if (!$stmt->fetch()) {
        return 'Usuário não encontrado.';
    }
    $update = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
    $update->execute([
        ':hash' => password_hash($next, PASSWORD_DEFAULT),
        ':id' => $userId,
    ]);
    return '';
}

function list_users(): array
{
    return db()->query(
        'SELECT id, username, role, crm_lead_id, created_at FROM users ORDER BY id ASC'
    )->fetchAll();
}

function delete_user_by_id(int $targetId, int $actorId): string
{
    if ($targetId === $actorId) {
        return 'Você não pode excluir a própria conta.';
    }
    $stmt = db()->prepare('SELECT id, role FROM users WHERE id = :id');
    $stmt->execute([':id' => $targetId]);
    $target = $stmt->fetch();
    if (!$target) {
        return 'Usuário não encontrado.';
    }
    if (($target['role'] ?? '') === 'admin' && admin_count() <= 1) {
        return 'Não é possível excluir o último administrador.';
    }
    $del = db()->prepare('DELETE FROM users WHERE id = :id');
    $del->execute([':id' => $targetId]);
    return '';
}
