<?php

declare(strict_types=1);

/**
 * @return list<array<string, mixed>>
 */
function images_list(PDO $pdo, bool $activeOnly = false, ?int $crmLeadId = null): array
{
    $sql = 'SELECT id, slug, url, title, description, price, promo_price, position, active, crm_lead_id
            FROM images WHERE ';
    $params = [];
    if ($crmLeadId === null) {
        $sql .= 'crm_lead_id IS NULL';
    } else {
        $sql .= 'crm_lead_id = :lead';
        $params[':lead'] = $crmLeadId;
    }
    if ($activeOnly) {
        $sql .= ' AND active = 1';
    }
    $sql .= ' ORDER BY position ASC, id ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

/**
 * Upsert de imagens por slug (agência: crm_lead_id IS NULL).
 *
 * @param list<array{slug: string, url: string, title?: string, description?: string, position?: int, active?: int}> $rows
 */
function images_upsert_by_slug(PDO $pdo, array $rows): void
{
    if ($rows === []) {
        return;
    }

    $now = gmdate('c');
    $select = $pdo->prepare('SELECT id FROM images WHERE slug = :slug AND crm_lead_id IS NULL LIMIT 1');
    $update = $pdo->prepare(
        'UPDATE images SET url = :url, title = :title, description = :description,
         position = COALESCE(:position, position), active = :active, updated_at = :updated_at
         WHERE slug = :slug AND crm_lead_id IS NULL'
    );
    $insert = $pdo->prepare(
        'INSERT INTO images (url, title, slug, description, position, active, created_at, updated_at, crm_lead_id)
         VALUES (:url, :title, :slug, :description, :position, :active, :created_at, :updated_at, NULL)'
    );

    foreach ($rows as $row) {
        $slug = trim((string) ($row['slug'] ?? ''));
        $url = trim((string) ($row['url'] ?? ''));
        if ($slug === '' || $url === '') {
            continue;
        }
        if (in_array($slug, ['favicon', 'logo'], true) && empty($row['force_brand'])) {
            continue;
        }
        if (str_starts_with($slug, 'video-')) {
            continue;
        }

        $title = trim((string) ($row['title'] ?? $slug));
        $description = trim((string) ($row['description'] ?? ''));
        $active = isset($row['active']) ? ((int) $row['active'] ? 1 : 0) : 1;
        $position = array_key_exists('position', $row) ? (int) $row['position'] : null;

        $select->execute([':slug' => $slug]);
        $exists = $select->fetch();

        if ($exists) {
            $update->execute([
                ':url' => $url,
                ':title' => $title !== '' ? $title : $slug,
                ':description' => $description,
                ':position' => $position,
                ':active' => $active,
                ':updated_at' => $now,
                ':slug' => $slug,
            ]);
            continue;
        }

        $insert->execute([
            ':url' => $url,
            ':title' => $title !== '' ? $title : $slug,
            ':slug' => $slug,
            ':description' => $description,
            ':position' => $position ?? 50,
            ':active' => $active,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}

/**
 * @param list<array{slug: string, url: string, title?: string, description?: string, position?: int, active?: int}> $rows
 */
function images_upsert_by_slug_for_lead(PDO $pdo, int $crmLeadId, array $rows): void
{
    if ($rows === [] || $crmLeadId <= 0) {
        return;
    }

    $now = gmdate('c');
    $select = $pdo->prepare('SELECT id FROM images WHERE slug = :slug AND crm_lead_id = :lead LIMIT 1');
    $update = $pdo->prepare(
        'UPDATE images SET url = :url, title = :title, description = :description,
         position = COALESCE(:position, position), active = :active, updated_at = :updated_at
         WHERE slug = :slug AND crm_lead_id = :lead'
    );
    $insert = $pdo->prepare(
        'INSERT INTO images (url, title, slug, description, position, active, created_at, updated_at, crm_lead_id)
         VALUES (:url, :title, :slug, :description, :position, :active, :created_at, :updated_at, :crm_lead_id)'
    );

    foreach ($rows as $row) {
        $slug = trim((string) ($row['slug'] ?? ''));
        $url = trim((string) ($row['url'] ?? ''));
        if ($slug === '' || $url === '') {
            continue;
        }
        if (str_starts_with($slug, 'video-')) {
            continue;
        }

        $title = trim((string) ($row['title'] ?? $slug));
        $description = trim((string) ($row['description'] ?? ''));
        $active = isset($row['active']) ? ((int) $row['active'] ? 1 : 0) : 1;
        $position = array_key_exists('position', $row) ? (int) $row['position'] : null;

        $select->execute([':slug' => $slug, ':lead' => $crmLeadId]);
        $exists = $select->fetch();

        if ($exists) {
            $update->execute([
                ':url' => $url,
                ':title' => $title !== '' ? $title : $slug,
                ':description' => $description,
                ':position' => $position,
                ':active' => $active,
                ':updated_at' => $now,
                ':slug' => $slug,
                ':lead' => $crmLeadId,
            ]);
            continue;
        }

        $insert->execute([
            ':url' => $url,
            ':title' => $title !== '' ? $title : $slug,
            ':slug' => $slug,
            ':description' => $description,
            ':position' => $position ?? 50,
            ':active' => $active,
            ':created_at' => $now,
            ':updated_at' => $now,
            ':crm_lead_id' => $crmLeadId,
        ]);
    }
}

/**
 * @param list<string> $keepSlugs
 */
function images_deactivate_unlisted(PDO $pdo, array $keepSlugs): void
{
    $keep = [];
    foreach ($keepSlugs as $slug) {
        $slug = trim((string) $slug);
        if ($slug !== '') {
            $keep[$slug] = true;
        }
    }
    $keep['favicon'] = true;
    $keep['logo'] = true;

    $now = gmdate('c');
    $rows = $pdo->query('SELECT id, slug, active FROM images WHERE crm_lead_id IS NULL')->fetchAll();
    $upd = $pdo->prepare(
        'UPDATE images SET active = 0, updated_at = :updated_at WHERE id = :id'
    );
    foreach ($rows as $row) {
        $slug = (string) ($row['slug'] ?? '');
        if ($slug === '' || isset($keep[$slug]) || str_starts_with($slug, 'video-')) {
            continue;
        }
        if ((int) ($row['active'] ?? 0) !== 1) {
            continue;
        }
        $upd->execute([
            ':updated_at' => $now,
            ':id' => (int) $row['id'],
        ]);
    }
}

/**
 * @param list<string> $keepSlugs
 */
function images_deactivate_unlisted_for_lead(PDO $pdo, int $crmLeadId, array $keepSlugs): void
{
    $keep = [];
    foreach ($keepSlugs as $slug) {
        $slug = trim((string) $slug);
        if ($slug !== '') {
            $keep[$slug] = true;
        }
    }
    $keep['favicon'] = true;
    $keep['logo'] = true;

    $now = gmdate('c');
    $stmt = $pdo->prepare('SELECT id, slug, active FROM images WHERE crm_lead_id = :lead');
    $stmt->execute([':lead' => $crmLeadId]);
    $upd = $pdo->prepare(
        'UPDATE images SET active = 0, updated_at = :updated_at WHERE id = :id AND crm_lead_id = :lead'
    );
    foreach ($stmt->fetchAll() as $row) {
        $slug = (string) ($row['slug'] ?? '');
        if ($slug === '' || isset($keep[$slug]) || str_starts_with($slug, 'video-')) {
            continue;
        }
        if ((int) ($row['active'] ?? 0) !== 1) {
            continue;
        }
        $upd->execute([
            ':updated_at' => $now,
            ':id' => (int) $row['id'],
            ':lead' => $crmLeadId,
        ]);
    }
}

/**
 * @param array<string, array{title?: string, description?: string, position?: int, file?: string}> $map
 * @return list<array{slug: string, url: string, title: string, description: string, position: int}>
 */
function preset_image_rows(string $presetId, array $map): array
{
    $base = 'assets/presets/' . $presetId . '/';
    $out = [];
    $i = 2;
    foreach ($map as $slug => $meta) {
        $file = (string) ($meta['file'] ?? ($slug . '.jpg'));
        $out[] = [
            'slug' => $slug,
            'url' => $base . $file,
            'title' => (string) ($meta['title'] ?? $slug),
            'description' => (string) ($meta['description'] ?? ''),
            'position' => (int) ($meta['position'] ?? $i),
            'active' => 1,
        ];
        $i++;
    }
    return $out;
}

function images_project_root(): string
{
    return dirname(__DIR__);
}

function images_local_fs_path(string $url): ?string
{
    $url = trim(str_replace('\\', '/', $url));
    if ($url === '' || preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, 'data:')) {
        return null;
    }

    $parts = [];
    foreach (explode('/', ltrim($url, '/')) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($parts);
            continue;
        }
        $parts[] = $part;
    }
    if ($parts === []) {
        return null;
    }

    return images_project_root() . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
}

function images_url_available(string $url): bool
{
    $url = trim($url);
    if ($url === '') {
        return false;
    }
    if (preg_match('#^(https?:)?//#i', $url) || str_starts_with($url, 'data:')) {
        return true;
    }
    $path = images_local_fs_path($url);
    return $path !== null && is_file($path);
}

/**
 * @return int quantidade desativada nesta chamada
 */
function images_deactivate_unavailable(PDO $pdo, ?int $crmLeadId = null): int
{
    static $ranAgency = false;
    static $ranLeads = [];

    if ($crmLeadId === null) {
        if ($ranAgency) {
            return 0;
        }
        $ranAgency = true;
        $rows = $pdo->query('SELECT id, url, active FROM images WHERE active = 1 AND crm_lead_id IS NULL')->fetchAll();
    } else {
        if (isset($ranLeads[$crmLeadId])) {
            return 0;
        }
        $ranLeads[$crmLeadId] = true;
        $stmt = $pdo->prepare('SELECT id, url, active FROM images WHERE active = 1 AND crm_lead_id = :lead');
        $stmt->execute([':lead' => $crmLeadId]);
        $rows = $stmt->fetchAll();
    }

    if ($rows === []) {
        return 0;
    }

    $upd = $pdo->prepare(
        'UPDATE images SET active = 0, updated_at = :updated_at WHERE id = :id'
    );
    $now = gmdate('c');
    $count = 0;
    foreach ($rows as $row) {
        if (images_url_available((string) ($row['url'] ?? ''))) {
            continue;
        }
        $upd->execute([
            ':updated_at' => $now,
            ':id' => (int) $row['id'],
        ]);
        $count++;
    }

    return $count;
}
