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

// Rate limit (arquivo): 8 hits / 10 min por IP
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateDir = dirname(__DIR__) . '/data/rate';
if (!is_dir($rateDir)) {
    @mkdir($rateDir, 0755, true);
}
$rateFile = $rateDir . '/payment-claim-' . hash('sha256', $ip) . '.json';
$now = time();
$hits = [];
if (is_file($rateFile)) {
    $prev = json_decode((string) file_get_contents($rateFile), true);
    if (is_array($prev)) {
        $hits = array_values(array_filter($prev, static fn ($t) => is_int($t) && ($now - $t) < 600));
    }
}
if (count($hits) >= 8) {
    http_response_code(429);
    echo json_encode([
        'ok' => false,
        'code' => 'rate_limited',
        'message' => 'Muitas tentativas. Aguarde alguns minutos.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$hits[] = $now;
@file_put_contents($rateFile, json_encode($hits));

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

$paymentMode = getenv('PAYMENT_MODE');
$paymentMode = is_string($paymentMode) && trim($paymentMode) !== '' ? strtolower(trim($paymentMode)) : 'manual';
if ($paymentMode !== 'manual') {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'code' => 'use_checkout',
        'message' => 'Reativação por PIX manual desligada. Use o link de pagamento do gateway.',
    ], JSON_UNESCAPED_UNICODE);
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
