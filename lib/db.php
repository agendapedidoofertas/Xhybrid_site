<?php

declare(strict_types=1);

function db_path(): string
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
    $path = $dir . 'site.sqlite';
    $legacy = $dir . 'atelier.sqlite';
    if (!is_file($path) && is_file($legacy)) {
        @rename($legacy, $path);
    }
    return $path;
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
    if (!in_array('crm_lead_id', $imgColNames, true)) {
        $pdo->exec('ALTER TABLE images ADD COLUMN crm_lead_id INTEGER NULL');
    }
    db_migrate_images_slug_scope($pdo);

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

    $svcCols = $pdo->query('PRAGMA table_info(services)')->fetchAll();
    $svcColNames = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $svcCols);
    if (!in_array('crm_lead_id', $svcColNames, true)) {
        $pdo->exec('ALTER TABLE services ADD COLUMN crm_lead_id INTEGER NULL');
    }

    // users: crm_lead_id for client_* roles (Phase 4)
    $userCols = $pdo->query('PRAGMA table_info(users)')->fetchAll();
    $userColNames = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $userCols);
    if (!in_array('crm_lead_id', $userColNames, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN crm_lead_id INTEGER NULL');
    }

    db_migrate_image_slugs($pdo);
    db_migrate_published_sites($pdo);
}

/**
 * Remove UNIQUE global de images.slug e passa a escopar por crm_lead_id
 * (agência = NULL → tratado como 0 no índice).
 */
function db_migrate_images_slug_scope(PDO $pdo): void
{
    $idx = $pdo->query("SELECT name, sql FROM sqlite_master WHERE type = 'index' AND tbl_name = 'images'")->fetchAll();
    $hasScoped = false;
    foreach ($idx as $row) {
        if (($row['name'] ?? '') === 'idx_images_slug_lead') {
            $hasScoped = true;
            break;
        }
    }
    if ($hasScoped) {
        return;
    }

    // Recria tabela sem UNIQUE em slug (bancos antigos)
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS images_new (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            url TEXT NOT NULL,
            title TEXT NOT NULL,
            slug TEXT NOT NULL,
            position INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            description TEXT NOT NULL DEFAULT \'\',
            price TEXT NOT NULL DEFAULT \'\',
            promo_price TEXT NOT NULL DEFAULT \'\',
            crm_lead_id INTEGER NULL
        )'
    );
    $cols = $pdo->query('PRAGMA table_info(images)')->fetchAll();
    $names = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $cols);
    $selectCols = ['id', 'url', 'title', 'slug', 'position', 'active', 'created_at', 'updated_at'];
    foreach (['description', 'price', 'promo_price', 'crm_lead_id'] as $opt) {
        if (in_array($opt, $names, true)) {
            $selectCols[] = $opt;
        }
    }
    $pdo->exec(
        'INSERT INTO images_new (' . implode(', ', $selectCols) . ')
         SELECT ' . implode(', ', $selectCols) . ' FROM images'
    );
    $pdo->exec('DROP TABLE images');
    $pdo->exec('ALTER TABLE images_new RENAME TO images');
    $pdo->exec(
        'CREATE UNIQUE INDEX IF NOT EXISTS idx_images_slug_lead
         ON images (slug, IFNULL(crm_lead_id, 0))'
    );
}

/** Sites de leads publicados pelo CRM (cópia independente da vitrine). */
function db_migrate_published_sites(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS published_sites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            crm_lead_id INTEGER NOT NULL UNIQUE,
            slug TEXT NOT NULL DEFAULT \'\',
            url_code TEXT NOT NULL DEFAULT \'\',
            site_active INTEGER NOT NULL DEFAULT 0,
            company_name TEXT NOT NULL DEFAULT \'\',
            category TEXT NOT NULL DEFAULT \'\',
            phone TEXT NOT NULL DEFAULT \'\',
            whatsapp TEXT NOT NULL DEFAULT \'\',
            email TEXT NOT NULL DEFAULT \'\',
            address_street TEXT NOT NULL DEFAULT \'\',
            address_number TEXT NOT NULL DEFAULT \'\',
            address_complement TEXT NOT NULL DEFAULT \'\',
            neighborhood TEXT NOT NULL DEFAULT \'\',
            city TEXT NOT NULL DEFAULT \'\',
            state TEXT NOT NULL DEFAULT \'\',
            postal_code TEXT NOT NULL DEFAULT \'\',
            maps_url TEXT NOT NULL DEFAULT \'\',
            website_url TEXT NOT NULL DEFAULT \'\',
            has_website INTEGER NOT NULL DEFAULT 0,
            instagram_url TEXT NOT NULL DEFAULT \'\',
            facebook_url TEXT NOT NULL DEFAULT \'\',
            opening_hours TEXT NOT NULL DEFAULT \'\',
            site_preset TEXT NOT NULL DEFAULT \'eletricista\',
            site_look TEXT NOT NULL DEFAULT \'\',
            site_theme TEXT NOT NULL DEFAULT \'\',
            site_font TEXT NOT NULL DEFAULT \'\',
            site_layout TEXT NOT NULL DEFAULT \'\',
            site_media TEXT NOT NULL DEFAULT \'\',
            payload_json TEXT NOT NULL DEFAULT \'\',
            plan_tier TEXT NOT NULL DEFAULT \'basic\',
            updated_at TEXT NOT NULL
        )'
    );
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS idx_published_slug_code ON published_sites(slug, url_code)');
    $pdo->exec('CREATE INDEX IF NOT EXISTS idx_published_active ON published_sites(site_active)');

    $psCols = $pdo->query('PRAGMA table_info(published_sites)')->fetchAll();
    $psNames = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $psCols);
    if (!in_array('plan_tier', $psNames, true)) {
        $pdo->exec('ALTER TABLE published_sites ADD COLUMN plan_tier TEXT NOT NULL DEFAULT \'basic\'');
    }
}

/** Renomeia slugs legados → nomes Xhybrid em bancos já existentes */
function db_migrate_image_slugs(PDO $pdo): void
{
    $map = [
        'casal' => 'about',
        'amigurumi' => 'landing',
        'manta' => 'corporate',
        'sousplat' => 'shop',
        'top' => 'maintenance',
        'bolsa' => 'integrations',
        'bebe' => 'branding',
    ];

    $select = $pdo->prepare('SELECT id, url, slug FROM images WHERE slug = :slug LIMIT 1');
    $exists = $pdo->prepare('SELECT id FROM images WHERE slug = :slug LIMIT 1');
    $update = $pdo->prepare(
        'UPDATE images SET slug = :new_slug, url = :url, updated_at = :updated_at WHERE id = :id'
    );
    $updateSvc = $pdo->prepare(
        'UPDATE services SET image_slug = :new_slug WHERE image_slug = :old_slug'
    );
    $now = gmdate('c');

    foreach ($map as $old => $new) {
        $select->execute([':slug' => $old]);
        $row = $select->fetch();
        if ($row) {
            $exists->execute([':slug' => $new]);
            if (!$exists->fetch()) {
                $url = (string) ($row['url'] ?? '');
                $url = str_replace(
                    ['/' . $old . '.jpg', '\\' . $old . '.jpg', $old . '.jpg'],
                    ['/' . $new . '.jpg', '\\' . $new . '.jpg', $new . '.jpg'],
                    $url
                );
                $update->execute([
                    ':new_slug' => $new,
                    ':url' => $url,
                    ':updated_at' => $now,
                    ':id' => (int) $row['id'],
                ]);
            }
        }

        $updateSvc->execute([':new_slug' => $new, ':old_slug' => $old]);
    }
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
        ['assets/about.jpg', 'Equipe Xhybrid', 'about', 3],
        ['assets/landing.jpg', 'Landing Page', 'landing', 4],
        ['assets/corporate.jpg', 'Site Corporativo', 'corporate', 5],
        ['assets/shop.jpg', 'Loja Online', 'shop', 6],
        ['assets/maintenance.jpg', 'Manutenção Contínua', 'maintenance', 7],
        ['assets/integrations.jpg', 'Integrações', 'integrations', 8],
        ['assets/branding.jpg', 'Identidade Web', 'branding', 9],
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
