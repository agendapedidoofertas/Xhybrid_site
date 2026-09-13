<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/images.php';
require_once dirname(__DIR__) . '/lib/security.php';

security_send_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

try {
    $pdo = db();
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
        $row = published_site_find_public($pdo, $slug, $leadId, $code);
        if (!$row || (int) ($row['site_active'] ?? 0) !== 1) {
            echo '[]';
            exit;
        }
    }
    images_deactivate_unavailable($pdo, $leadId);
    $rows = [];
    foreach (images_list($pdo, false, $leadId) as $row) {
        if (!images_url_available((string) ($row['url'] ?? ''))) {
            continue;
        }
        unset($row['active'], $row['crm_lead_id'], $row['position']);
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    api_json_error('Falha ao carregar imagens', $e);
}
