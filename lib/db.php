<?php

declare(strict_types=1);

function db_path(): string
{
    return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'atelier.sqlite';
}

function sqlite_driver_loaded(): bool
{
    return in_array('sqlite', PDO::getAvailableDrivers(), true);
}

function sqlite_setup_help_html(): string
{
    $ini = php_ini_loaded_file() ?: 'não encontrado (rode php --ini no terminal)';
    $ext = ini_get('extension_dir') ?: 'ext';

    return '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>SQLite não habilitado</title>
<style>body{font-family:system-ui,sans-serif;max-width:40rem;margin:3rem auto;padding:0 1.25rem;line-height:1.5;color:#2c2118;background:#f6f0e6;}code,pre{background:#efe6d6;padding:.15rem .4rem;border-radius:.35rem;}pre{padding:1rem;overflow:auto;}h1{font-size:1.6rem;}</style></head><body>
<h1>Falta o driver SQLite no PHP</h1>
<p>O PHP está rodando, mas a extensão <code>pdo_sqlite</code> não está ativa. Sem ela o painel e a API não funcionam.</p>
<p><strong>php.ini em uso:</strong><br><code>' . htmlspecialchars($ini, ENT_QUOTES, 'UTF-8') . '</code></p>
<p><strong>Pasta de extensões:</strong><br><code>' . htmlspecialchars((string) $ext, ENT_QUOTES, 'UTF-8') . '</code></p>
<ol>
<li>Abra o <code>php.ini</code> acima no Bloco de Notas.</li>
<li>Confirme que existe (sem <code>;</code> na frente):<br>
<pre>extension_dir = "ext"
extension=pdo_sqlite
extension=sqlite3</pre></li>
<li>Salve o arquivo.</li>
<li>Pare o servidor (<kbd>Ctrl+C</kbd> no terminal) e inicie de novo:<br>
<pre>php -S localhost:8000 router.php</pre></li>
</ol>
<p>Para conferir: <code>php -m</code> deve listar <code>pdo_sqlite</code>.</p>
</body></html>';
}

function require_sqlite_driver(): void
{
    if (sqlite_driver_loaded()) {
        return;
    }

    if (PHP_SAPI !== 'cli') {
        http_response_code(500);
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        if (str_contains($accept, 'application/json') || str_ends_with($_SERVER['SCRIPT_NAME'] ?? '', 'images.php')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'pdo_sqlite não está habilitado no PHP. Ative extension=pdo_sqlite no php.ini.']);
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo sqlite_setup_help_html();
        exit;
    }

    throw new RuntimeException(
        'pdo_sqlite não está habilitado. Edite o php.ini e ative extension=pdo_sqlite e extension=sqlite3.'
    );
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    require_sqlite_driver();

    $dir = dirname(db_path());
    if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
        throw new RuntimeException('Não foi possível criar a pasta data/.');
    }

    try {
        $pdo = new PDO('sqlite:' . db_path(), null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        if (str_contains(strtolower($e->getMessage()), 'could not find driver')) {
            require_sqlite_driver();
        }
        throw $e;
    }
    $pdo->exec('PRAGMA foreign_keys = ON');
    db_migrate($pdo);
    db_seed_images($pdo);
    db_ensure_brand_assets($pdo);
    require_once __DIR__ . '/settings.php';
    settings_seed($pdo);
    require_once __DIR__ . '/services.php';
    services_seed($pdo);

    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT \'admin\',
            created_at TEXT NOT NULL
        )'
    );

    // Bancos antigos: adiciona role sem quebrar usuários existentes
    $cols = $pdo->query('PRAGMA table_info(users)')->fetchAll();
    $hasRole = false;
    foreach ($cols as $col) {
        if (($col['name'] ?? '') === 'role') {
            $hasRole = true;
            break;
        }
    }
    if (!$hasRole) {
        $pdo->exec('ALTER TABLE users ADD COLUMN role TEXT NOT NULL DEFAULT \'admin\'');
        $pdo->exec('UPDATE users SET role = \'admin\' WHERE role IS NULL OR role = \'\'');
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT NOT NULL,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            position INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )'
    );

    // Bancos antigos: descrição e preços editáveis na galeria
    $imgCols = $pdo->query('PRAGMA table_info(images)')->fetchAll();
    $imgColNames = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $imgCols);
    if (!in_array('description', $imgColNames, true)) {
        $pdo->exec('ALTER TABLE images ADD COLUMN description TEXT NOT NULL DEFAULT \'\'');
    }
    if (!in_array('price', $imgColNames, true)) {
        $pdo->exec('ALTER TABLE images ADD COLUMN price TEXT NOT NULL DEFAULT \'\'');
    }
    if (!in_array('promo_price', $imgColNames, true)) {
        $pdo->exec('ALTER TABLE images ADD COLUMN promo_price TEXT NOT NULL DEFAULT \'\'');
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS settings (
            setting_key TEXT PRIMARY KEY NOT NULL,
            value TEXT NOT NULL DEFAULT \'\',
            updated_at TEXT NOT NULL
        )'
    );

    // Migração: tabela antiga usava coluna "key" (palavra reservada em alguns ambientes)
    $settingsCols = $pdo->query('PRAGMA table_info(settings)')->fetchAll();
    $settingsColNames = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $settingsCols);
    if (in_array('key', $settingsColNames, true) && !in_array('setting_key', $settingsColNames, true)) {
        $pdo->exec('ALTER TABLE settings RENAME TO settings_legacy_key');
        $pdo->exec(
            'CREATE TABLE settings (
                setting_key TEXT PRIMARY KEY NOT NULL,
                value TEXT NOT NULL DEFAULT \'\',
                updated_at TEXT NOT NULL
            )'
        );
        $pdo->exec(
            'INSERT OR IGNORE INTO settings (setting_key, value, updated_at)
             SELECT "key", value, updated_at FROM settings_legacy_key'
        );
        $pdo->exec('DROP TABLE settings_legacy_key');
    }

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL DEFAULT \'\',
            description TEXT NOT NULL DEFAULT \'\',
            image_slug TEXT NOT NULL DEFAULT \'\',
            category TEXT NOT NULL DEFAULT \'\',
            position INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        )'
    );
}

function db_seed_images(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM images')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $now = gmdate('c');
    $rows = [
        ['favicon.svg', 'Favicon Xhybrid', 'favicon', 0],
        ['assets/logo-xhybrid.svg', 'Logo Xhybrid', 'logo', 1],
        ['assets/hero.jpg', 'Hero — desenvolvimento', 'hero', 2],
        ['assets/casal.jpg', 'Equipe Xhybrid', 'casal', 3],
        ['assets/amigurumi.jpg', 'Landing Page', 'amigurumi', 4],
        ['assets/manta.jpg', 'Site Corporativo', 'manta', 5],
        ['assets/sousplat.jpg', 'Loja Online', 'sousplat', 6],
        ['assets/top.jpg', 'Manutenção Contínua', 'top', 7],
        ['assets/bolsa.jpg', 'Integrações', 'bolsa', 8],
        ['assets/bebe.jpg', 'Identidade Web', 'bebe', 9],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO images (url, title, slug, position, active, created_at, updated_at)
         VALUES (:url, :title, :slug, :position, 1, :created_at, :updated_at)'
    );

    foreach ($rows as [$url, $title, $slug, $position]) {
        $stmt->execute([
            ':url' => $url,
            ':title' => $title,
            ':slug' => $slug,
            ':position' => $position,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}

/** Garante logo/favicon padrão em bancos já existentes */
function db_ensure_brand_assets(PDO $pdo): void
{
    $now = gmdate('c');
    $defaults = [
        'favicon' => ['favicon.svg', 'Favicon Xhybrid', 0],
        'logo' => ['assets/logo-xhybrid.svg', 'Logo Xhybrid', 1],
    ];
    $check = $pdo->prepare('SELECT id FROM images WHERE slug = :slug LIMIT 1');
    $insert = $pdo->prepare(
        'INSERT INTO images (url, title, slug, position, active, created_at, updated_at)
         VALUES (:url, :title, :slug, :position, 1, :created_at, :updated_at)'
    );
    foreach ($defaults as $slug => [$url, $title, $position]) {
        $check->execute([':slug' => $slug]);
        if ($check->fetch()) {
            continue;
        }
        $insert->execute([
            ':url' => $url,
            ':title' => $title,
            ':slug' => $slug,
            ':position' => $position,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}
