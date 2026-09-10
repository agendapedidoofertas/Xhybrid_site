<?php

declare(strict_types=1);

/**
 * Upsert de imagens por slug (não apaga logo/favicon nem vídeos).
 *
 * @param list<array{slug: string, url: string, title?: string, description?: string, position?: int, active?: int}> $rows
 */
function images_upsert_by_slug(PDO $pdo, array $rows): void
{
    if ($rows === []) {
        return;
    }

    $now = gmdate('c');
    $select = $pdo->prepare('SELECT id FROM images WHERE slug = :slug LIMIT 1');
    $update = $pdo->prepare(
        'UPDATE images SET url = :url, title = :title, description = :description,
         position = COALESCE(:position, position), active = :active, updated_at = :updated_at
         WHERE slug = :slug'
    );
    $insert = $pdo->prepare(
        'INSERT INTO images (url, title, slug, description, position, active, created_at, updated_at)
         VALUES (:url, :title, :slug, :description, :position, :active, :created_at, :updated_at)'
    );

    foreach ($rows as $row) {
        $slug = trim((string) ($row['slug'] ?? ''));
        $url = trim((string) ($row['url'] ?? ''));
        if ($slug === '' || $url === '') {
            continue;
        }
        // Nunca sobrescrever marca/vídeo por engano em packs sem esses slugs
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
 * Desativa imagens de galeria que não estão no pacote do preset
 * (mantém logo, favicon e vídeos).
 *
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
    $rows = $pdo->query('SELECT id, slug, active FROM images')->fetchAll();
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
 * Monta lista de imagens de um pacote de preset em assets/presets/{id}/.
 *
 * @param array<string, array{title?: string, description?: string, position?: int, file?: string}> $map slug => meta
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

/** Raiz do projeto (pasta com assets/, data/, etc.). */
function images_project_root(): string
{
    return dirname(__DIR__);
}

/**
 * Resolve path local de uma URL relativa do site (sem path traversal).
 * Retorna null se for URL remota/data ou vazia.
 */
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

/**
 * True se a mídia pode aparecer no site:
 * - URL http(s)/data (Drive etc.)
 * - path local com arquivo existente no disco
 */
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
 * Desativa automaticamente imagens ativas sem URL ou sem arquivo local.
 * URLs remotas (Drive) permanecem ativas.
 *
 * @return int quantidade desativada nesta chamada
 */
function images_deactivate_unavailable(PDO $pdo): int
{
    static $ran = false;
    if ($ran) {
        return 0;
    }
    $ran = true;

    $rows = $pdo->query('SELECT id, url, active FROM images WHERE active = 1')->fetchAll();
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
