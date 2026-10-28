<?php

declare(strict_types=1);

/**
 * Captura preliminar de Lead no Checkout (Anti-abandono de carrinho)
 * Disparado silenciosamente via AJAX quando o cliente preenche o WhatsApp/Email.
 */

require_once dirname(__DIR__) . '/lib/security.php';
require_once dirname(__DIR__) . '/lib/crm_bridge.php';
require_once dirname(__DIR__) . '/lib/plans.php';

security_send_headers();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'POST only']);
    exit;
}

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

// Anti-bot: se honeypot foi preenchido, ignora
if (!empty($data['hp_token'])) {
    echo json_encode(['ok' => true]);
    exit;
}

$whatsapp = preg_replace('/\D+/', '', (string) ($data['whatsapp'] ?? '')) ?? '';
$company = trim(str_replace(['<', '>'], '', (string) ($data['company'] ?? '')));
$email = trim((string) ($data['email'] ?? ''));
$plan = plan_normalize((string) ($data['plan'] ?? 'basic'));

// Exige ao menos um WhatsApp válido para poder recuperar depois
if (strlen($whatsapp) < 10) {
    echo json_encode(['ok' => false, 'error' => 'WhatsApp incompleto']);
    exit;
}

if (!crm_bridge_configured()) {
    echo json_encode(['ok' => false, 'error' => 'CRM bridge indisponível']);
    exit;
}

try {
    $crmRoot = crm_bridge_root();
    require_once $crmRoot . '/lib/db.php';
    require_once $crmRoot . '/lib/crm/bootstrap.php';

    $pdo = db();

    // Verifica se já existe um lead recente com este telefone para não duplicar
    $stmt = $pdo->prepare('SELECT id, payment_status FROM leads WHERE (whatsapp = :w OR phone = :w) ORDER BY id DESC LIMIT 1');
    $stmt->execute([':w' => $whatsapp]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing && ($existing['payment_status'] === 'paid' || $existing['payment_status'] === 'active')) {
        echo json_encode(['ok' => true, 'lead_id' => (int) $existing['id'], 'existing' => true]);
        exit;
    }

    if ($existing) {
        $leadId = (int) $existing['id'];
        $up = $pdo->prepare('UPDATE leads SET company_name = COALESCE(NULLIF(:comp, ""), company_name), email = COALESCE(NULLIF(:email, ""), email), plan_tier = :plan, updated_at = :now WHERE id = :id');
        $up->execute([
            ':comp' => $company,
            ':email' => $email,
            ':plan' => $plan,
            ':now' => gmdate('c'),
            ':id' => $leadId,
        ]);
    } else {
        $leadId = crm_lead_save_manual($pdo, [
            'company_name' => $company !== '' ? $company : 'Empresa em Checkout',
            'email' => $email,
            'whatsapp' => $whatsapp,
            'phone' => $whatsapp,
            'plan_tier' => $plan,
            'payment_status' => 'draft',
            'status' => 'CARRINHO_ABANDONADO',
            'notes' => 'Captura preliminar no formulário de checkout',
        ]);
    }

    echo json_encode(['ok' => true, 'lead_id' => $leadId]);
} catch (Throwable $e) {
    error_log('[checkout_draft] ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'Falha interna ao salvar rascunho']);
}
