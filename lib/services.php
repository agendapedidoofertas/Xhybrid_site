<?php

declare(strict_types=1);

function services_seed(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $now = gmdate('c');
    $rows = [
        ['Landing Page', 'Página focada em conversão para campanhas e lançamentos.', 'amigurumi', 'Sites', 0],
        ['Site Corporativo', 'Presença institucional clara, rápida e profissional.', 'manta', 'Sites', 1],
        ['Loja Online', 'Vitrine digital com catálogo e fluxo de contato.', 'sousplat', 'E-commerce', 2],
        ['Manutenção', 'Atualizações, backups e performance contínua.', 'top', 'Suporte', 3],
        ['Integrações', 'WhatsApp, formulários e automações no dia a dia.', 'bolsa', 'Tecnologia', 4],
        ['Identidade Web', 'Visual alinhado à marca em todas as páginas.', 'bebe', 'Design', 5],
    ];

    $stmt = $pdo->prepare(
        'INSERT INTO services (title, description, image_slug, category, position, active, created_at, updated_at)
         VALUES (:title, :description, :image_slug, :category, :position, 1, :created_at, :updated_at)'
    );

    foreach ($rows as [$title, $description, $slug, $category, $position]) {
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_slug' => $slug,
            ':category' => $category,
            ':position' => $position,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}

function services_list(PDO $pdo, bool $activeOnly = false): array
{
    $sql = 'SELECT id, title, description, image_slug, category, position, active
            FROM services';
    if ($activeOnly) {
        $sql .= ' WHERE active = 1';
    }
    $sql .= ' ORDER BY position ASC, id ASC';
    return $pdo->query($sql)->fetchAll();
}

function services_get(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function services_normalize_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

function services_save(PDO $pdo, array $data, ?int $id = null): void
{
    $now = gmdate('c');
    $title = trim(str_replace(['<', '>'], '', (string) ($data['title'] ?? '')));
    $description = trim(str_replace(['<', '>'], '', (string) ($data['description'] ?? '')));
    $category = trim(str_replace(['<', '>'], '', (string) ($data['category'] ?? '')));
    $imageSlug = services_normalize_slug((string) ($data['image_slug'] ?? ''));
    $position = (int) ($data['position'] ?? 0);
    $active = !empty($data['active']) ? 1 : 0;

    if (function_exists('mb_substr')) {
        $title = mb_substr($title, 0, 80, 'UTF-8');
        $description = mb_substr($description, 0, 220, 'UTF-8');
        $category = mb_substr($category, 0, 40, 'UTF-8');
    } else {
        $title = substr($title, 0, 80);
        $description = substr($description, 0, 220);
        $category = substr($category, 0, 40);
    }

    if ($id) {
        $stmt = $pdo->prepare(
            'UPDATE services SET title = :title, description = :description, image_slug = :image_slug,
             category = :category, position = :position, active = :active, updated_at = :updated_at
             WHERE id = :id'
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image_slug' => $imageSlug,
            ':category' => $category,
            ':position' => $position,
            ':active' => $active,
            ':updated_at' => $now,
            ':id' => $id,
        ]);
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO services (title, description, image_slug, category, position, active, created_at, updated_at)
         VALUES (:title, :description, :image_slug, :category, :position, :active, :created_at, :updated_at)'
    );
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':image_slug' => $imageSlug,
        ':category' => $category,
        ':position' => $position,
        ':active' => $active,
        ':created_at' => $now,
        ':updated_at' => $now,
    ]);
}

function services_delete(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM services WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function services_replace_all(PDO $pdo, array $rows): void
{
    $pdo->exec('DELETE FROM services');
    $now = gmdate('c');
    $stmt = $pdo->prepare(
        'INSERT INTO services (title, description, image_slug, category, position, active, created_at, updated_at)
         VALUES (:title, :description, :image_slug, :category, :position, :active, :created_at, :updated_at)'
    );
    foreach ($rows as $i => $row) {
        $stmt->execute([
            ':title' => (string) ($row['title'] ?? ''),
            ':description' => (string) ($row['description'] ?? ''),
            ':image_slug' => services_normalize_slug((string) ($row['image_slug'] ?? '')),
            ':category' => (string) ($row['category'] ?? ''),
            ':position' => (int) ($row['position'] ?? $i),
            ':active' => isset($row['active']) ? (int) (bool) $row['active'] : 1,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }
}
