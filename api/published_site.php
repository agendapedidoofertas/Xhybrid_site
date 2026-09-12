<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

$slug = strtolower(trim((string) ($_GET['slug'] ?? '')));
$code = strtolower(trim((string) ($_GET['code'] ?? '')));
$leadId = (int) ($_GET['lead_id'] ?? $_GET['id'] ?? 0);

if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || !preg_match('/^[a-z]$/', $code) || $leadId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetros inválidos'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $row = published_site_find_public(db(), $slug, $leadId, $code);
    if (!$row || (int) ($row['site_active'] ?? 0) !== 1) {
        http_response_code(404);
        echo json_encode(['error' => 'Site não encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $settings = published_site_settings_overlay($row);
    echo json_encode([
        'slug' => $slug,
        'lead_id' => $leadId,
        'url_code' => $code,
        'site_preset' => (string) ($row['site_preset'] ?? ''),
        'settings' => $settings,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Falha ao carregar site publicado',
        'detail' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
