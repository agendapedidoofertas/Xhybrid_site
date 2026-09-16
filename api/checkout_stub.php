<?php

declare(strict_types=1);

/**
 * Captura pública mínima → CRM (cria lead + cobrança se stub/live).
 * Requer CRM bridge configurado.
 */

require_once dirname(__DIR__) . '/lib/security.php';
require_once dirname(__DIR__) . '/lib/crm_bridge.php';

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

$plan = strtolower(trim((string) ($data['plan'] ?? 'basic')));
if (!in_array($plan, ['basic', 'medium', 'pro'], true)) {
    $plan = 'basic';
}
$company = trim(str_replace(['<', '>'], '', (string) ($data['company'] ?? '')));
$email = trim((string) ($data['email'] ?? ''));
$whatsapp = preg_replace('/\D+/', '', (string) ($data['whatsapp'] ?? '')) ?? '';

if ($company === '' || strlen($company) < 2) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Informe a empresa.']);
    exit;
}

if (!crm_bridge_configured()) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'CRM não configurado neste servidor.']);
    exit;
}

try {
    $crmRoot = crm_bridge_root();
    require_once $crmRoot . '/lib/db.php';
    require_once $crmRoot . '/lib/crm/bootstrap.php';
    require_once $crmRoot . '/lib/payment/PaymentService.php';

    $pdo = db();
    $id = crm_lead_save_manual($pdo, [
        'company_name' => $company,
        'email' => $email,
        'whatsapp' => $whatsapp,
        'phone' => $whatsapp,
        'plan_tier' => $plan,
        'payment_status' => 'pending',
        'status' => 'NOVO',
    ]);

    $checkout = '';
    $msg = 'Lead #' . $id . ' criado. Em modo manual a equipe confirma o pagamento.';
    if (payment_mode() !== 'manual') {
        $created = payment_create_charge_for_lead($pdo, $id, $plan);
        $checkout = $created['checkout_url'];
        $msg = 'Lead criado. Conclua o pagamento no link.';
    }
    crm_ops_log($id, 'checkout_public', 'Checkout público', ['plan' => $plan]);

    echo json_encode([
        'ok' => true,
        'lead_id' => $id,
        'checkout_url' => $checkout,
        'message' => $msg,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('[checkout_stub] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Falha ao criar lead.']);
}
