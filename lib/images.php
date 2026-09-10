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
