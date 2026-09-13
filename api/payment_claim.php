<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';
require_once dirname(__DIR__) . '/lib/security.php';
require_once dirname(__DIR__) . '/lib/payment_grace.php';

security_send_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$data = [];
if (is_string($raw) && $raw !== '') {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
if ($data === []) {
    $data = $_POST;
}

$slug = strtolower(trim((string) ($data['slug'] ?? '')));
$code = strtolower(trim((string) ($data['code'] ?? $data['letter'] ?? '')));
$leadId = (int) ($data['lead_id'] ?? $data['id'] ?? 0);

if ($slug === '' || !preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || !preg_match('/^[a-z]$/', $code) || $leadId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'code' => 'bad_request', 'message' => 'Parâmetros inválidos'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $result = payment_claim_self(db(), $leadId, $slug, $code);
    $http = $result['ok'] ? 200 : (($result['code'] ?? '') === 'need_whatsapp' ? 403 : 400);
    http_response_code($http);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    api_json_error('Falha ao processar pagamento', $e);
}
