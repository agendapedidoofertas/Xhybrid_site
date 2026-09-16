<?php

declare(strict_types=1);

/**
 * Convite de usuário cliente (Medium/Pro) — token one-shot.
 * Staff gera em users.php; convidado abre invite.php?token=...
 */

require_once dirname(__DIR__) . '/lib/db.php';

function invite_migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS user_invites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT NOT NULL UNIQUE,
            crm_lead_id INTEGER NOT NULL,
            role TEXT NOT NULL DEFAULT \'client_medium\',
            email TEXT NOT NULL DEFAULT \'\',
            created_by INTEGER,
            expires_at TEXT NOT NULL,
            used_at TEXT NOT NULL DEFAULT \'\',
            created_at TEXT NOT NULL
        )'
    );
}

/**
 * @return array{token:string,url:string}
 */
function invite_create(PDO $pdo, int $crmLeadId, string $role, int $createdBy, string $email = ''): array
{
    invite_migrate($pdo);
    if (!in_array($role, ['client_medium', 'client_pro'], true)) {
        throw new InvalidArgumentException('Role de convite inválida.');
    }
    $token = bin2hex(random_bytes(24));
    $now = gmdate('c');
    $expires = gmdate('c', time() + 7 * 86400);
    $pdo->prepare(
        'INSERT INTO user_invites (token, crm_lead_id, role, email, created_by, expires_at, used_at, created_at)
         VALUES (:token, :crm_lead_id, :role, :email, :created_by, :expires_at, \'\', :created_at)'
    )->execute([
        ':token' => $token,
        ':crm_lead_id' => $crmLeadId,
        ':role' => $role,
        ':email' => $email,
        ':created_by' => $createdBy,
        ':expires_at' => $expires,
        ':created_at' => $now,
    ]);
    $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
        . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    return ['token' => $token, 'url' => rtrim($base, '/') . '/admin/invite.php?token=' . urlencode($token)];
}

function invite_consume(PDO $pdo, string $token, string $username, string $password): void
{
    invite_migrate($pdo);
    $stmt = $pdo->prepare('SELECT * FROM user_invites WHERE token = :t LIMIT 1');
    $stmt->execute([':t' => $token]);
    $row = $stmt->fetch();
    if (!$row) {
        throw new RuntimeException('Convite inválido.');
    }
    if (trim((string) ($row['used_at'] ?? '')) !== '') {
        throw new RuntimeException('Convite já utilizado.');
    }
    if ((string) $row['expires_at'] < gmdate('c')) {
        throw new RuntimeException('Convite expirado.');
    }
    require_once __DIR__ . '/auth.php';
    create_user($username, $password, (string) $row['role'], (int) $row['crm_lead_id']);
    $pdo->prepare('UPDATE user_invites SET used_at = :u WHERE id = :id')
        ->execute([':u' => gmdate('c'), ':id' => (int) $row['id']]);
}
