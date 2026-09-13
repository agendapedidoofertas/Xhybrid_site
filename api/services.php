<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/services.php';
require_once dirname(__DIR__) . '/lib/security.php';

security_send_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

try {
    $leadId = null;
    if (isset($_GET['lead_id']) && (int) $_GET['lead_id'] > 0) {
        $leadId = (int) $_GET['lead_id'];
        require_once dirname(__DIR__) . '/lib/published_sites.php';
        $slug = strtolower(trim((string) ($_GET['slug'] ?? '')));
        $code = strtolower(trim((string) ($_GET['code'] ?? '')));
        if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || !preg_match('/^[a-z]$/', $code)) {
            echo '[]';
            exit;
        }
        $row = published_site_find_public(db(), $slug, $leadId, $code);
        if (!$row || (int) ($row['site_active'] ?? 0) !== 1) {
            echo '[]';
            exit;
        }
    }
    $rows = services_list(db(), true, $leadId);
    $out = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'title' => (string) $row['title'],
            'description' => (string) $row['description'],
            'image_slug' => (string) $row['image_slug'],
            'category' => (string) $row['category'],
            'position' => (int) $row['position'],
        ];
    }, $rows);
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    api_json_error('Falha ao carregar serviços', $e);
}
