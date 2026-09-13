<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/mailer.php';
require_once dirname(__DIR__) . '/lib/security.php';

security_send_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método não permitido']);
    exit;
}

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

// Honeypot
if (trim((string) ($data['website'] ?? '')) !== '') {
    echo json_encode(['ok' => true]);
    exit;
}

$nome = trim(str_replace(['<', '>'], '', (string) ($data['nome'] ?? '')));
$mensagem = trim(str_replace(['<', '>'], '', (string) ($data['mensagem'] ?? '')));
if (function_exists('mb_substr')) {
    $nome = mb_substr($nome, 0, 80, 'UTF-8');
    $mensagem = mb_substr($mensagem, 0, 4000, 'UTF-8');
} else {
    $nome = substr($nome, 0, 80);
    $mensagem = substr($mensagem, 0, 4000);
}

if ($nome === '' || $mensagem === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Preencha nome e mensagem.']);
    exit;
}

// Rate limit simples em arquivo (não usa SQLite)
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateDir = dirname(__DIR__) . '/data/rate';
if (!is_dir($rateDir)) {
    @mkdir($rateDir, 0755, true);
}
$rateFile = $rateDir . '/' . hash('sha256', $ip) . '.json';
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
    echo json_encode(['ok' => false, 'error' => 'Muitas tentativas. Aguarde alguns minutos.']);
    exit;
}
$hits[] = $now;
@file_put_contents($rateFile, json_encode($hits));

$settings = settings_all(db());
if (!mailer_configured($settings)) {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'Formulário ainda sem SMTP. Configure em Admin → Contato.',
    ]);
    exit;
}

$from = trim((string) ($settings['smtp_from'] ?? ''));
if ($from === '') {
    $from = trim((string) ($settings['email'] ?? ''));
}
$to = trim((string) ($settings['smtp_to'] ?? ''));
if ($to === '') {
    $to = trim((string) ($settings['email'] ?? ''));
}

$brand = trim((string) ($settings['brand_name'] ?? 'Site'));
$result = mailer_send([
    'host' => (string) ($settings['smtp_host'] ?? ''),
    'port' => (int) ($settings['smtp_port'] ?? 587) ?: 587,
    'user' => (string) ($settings['smtp_user'] ?? ''),
    'pass' => (string) ($settings['smtp_pass'] ?? ''),
    'from' => $from,
    'to' => $to,
    'subject' => 'Contato do site — ' . $nome . ' (' . $brand . ')',
    'body' => "Nome: {$nome}\nIP: {$ip}\n\n{$mensagem}\n",
]);

if (!$result['ok']) {
    error_log('[xhybrid contact] ' . ($result['error'] ?? 'send failed'));
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'Não foi possível enviar a mensagem. Tente novamente.']);
    exit;
}

echo json_encode(['ok' => true, 'message' => 'Mensagem enviada.']);
