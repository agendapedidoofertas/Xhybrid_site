<?php

declare(strict_types=1);

/**
 * Captura pública mínima -> CRM (cria lead + cobrança se stub/live).
 * Requer CRM bridge configurado.
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

// Rate limit por IP (12 tentativas a cada 10 minutos)
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$rateDir = dirname(__DIR__) . '/data/rate';
if (!is_dir($rateDir)) {
    @mkdir($rateDir, 0755, true);
}
$rateFile = $rateDir . '/checkout-' . hash('sha256', $ip) . '.json';
$now = time();
$hits = [];
if (is_file($rateFile)) {
    $prev = json_decode((string) file_get_contents($rateFile), true);
    if (is_array($prev)) {
        $hits = array_values(array_filter($prev, static fn ($t) => is_int($t) && ($now - $t) < 600));
    }
}
if (count($hits) >= 12) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Muitas tentativas. Aguarde alguns minutos.']);
    exit;
}
$hits[] = $now;
@file_put_contents($rateFile, json_encode($hits));

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

// Anti-bot: Verificação do campo Honeypot invisível
if (!empty($data['hp_token'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Falha na verificação de segurança.']);
    exit;
}

// Anti-bot: Verificação do Cloudflare Turnstile (se configurado)
$turnstileSecret = getenv('CLOUDFLARE_TURNSTILE_SECRET_KEY') ?: '';
$turnstileFile = dirname(__DIR__) . '/data/turnstile_config.php';
if ($turnstileSecret === '' && is_file($turnstileFile)) {
    $tcfg = include $turnstileFile;
    if (is_array($tcfg) && !empty($tcfg['secret_key'])) {
        $turnstileSecret = (string) $tcfg['secret_key'];
    }
}

if ($turnstileSecret !== '') {
    $turnstileToken = (string) ($data['cf-turnstile-response'] ?? '');
    if ($turnstileToken === '') {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Por favor, confirme que você não é um robô.']);
        exit;
    }
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret' => $turnstileSecret,
            'response' => $turnstileToken,
            'remoteip' => $ip,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
    ]);
    $cfRes = curl_exec($ch);
    curl_close($ch);
    $cfData = json_decode((string) $cfRes, true);
    if (empty($cfData['success'])) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Verificação anti-robô falhou. Tente recarregar a página.']);
        exit;
    }
}

$termsAccepted = !empty($data['terms_accepted']) && (
    $data['terms_accepted'] === true
    || $data['terms_accepted'] === 1
    || $data['terms_accepted'] === '1'
    || $data['terms_accepted'] === 'true'
);
if (!$termsAccepted) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Aceite os Termos de Serviço para continuar.']);
    exit;
}

$plan = plan_normalize((string) ($data['plan'] ?? 'basic'));
$provider = 'asaas';

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/public_offer.php';
$publicOffer = public_offer_get(db());
if (!empty($publicOffer['plans'][$plan]['hidden'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Este plano não está disponível no momento.']);
    exit;
}

$company = trim(str_replace(['<', '>'], '', (string) ($data['company'] ?? '')));
$email = trim((string) ($data['email'] ?? ''));
$whatsapp = preg_replace('/\D+/', '', (string) ($data['whatsapp'] ?? '')) ?? '';
$cpfCnpj = preg_replace('/\D+/', '', (string) ($data['cpf_cnpj'] ?? '')) ?? '';

if ($company === '' || strlen($company) < 2) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Informe a empresa.']);
    exit;
}

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Informe um e-mail válido.']);
    exit;
}

if (strlen($whatsapp) < 10 || strlen($whatsapp) > 15) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'error' => 'Informe um número de WhatsApp válido com DDD.']);
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

    if ($provider === 'asaas' && payment_mode() === 'live' && strlen($cpfCnpj) !== 11 && strlen($cpfCnpj) !== 14) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Informe um CPF (11 dígitos) ou CNPJ (14 dígitos) válido.']);
        exit;
    }

    $pdo = db();
    $id = crm_lead_save_manual($pdo, [
        'company_name' => $company,
        'email' => $email,
        'whatsapp' => $whatsapp,
        'phone' => $whatsapp,
        'plan_tier' => $plan,
        'payment_status' => 'pending',
        'status' => 'NOVO',
        'notes' => $cpfCnpj !== '' ? ('Documento: ' . $cpfCnpj) : '',
    ]);

    $checkout = '';
    $msg = 'Lead #' . $id . ' criado. Em modo manual a equipe confirma o pagamento.';
    if (payment_mode() !== 'manual') {
        $created = payment_create_charge_for_lead($pdo, $id, $plan, $provider, [
            'cpf_cnpj' => $cpfCnpj,
            'whatsapp' => $whatsapp,
        ]);
        $checkout = $created['checkout_url'];
        $msg = 'Lead criado. Conclua o pagamento no link (' . $provider . ').';
    }
    crm_ops_log($id, 'checkout_public', 'Checkout público', [
        'plan' => $plan,
        'provider' => $provider,
        'terms_accepted' => true,
        'has_cpf_cnpj' => $cpfCnpj !== '',
    ]);

    // Notificação imediata para admin via WhatsApp Bot
    try {
        require_once $crmRoot . '/lib/whatsapp/WhatsAppService.php';
        whatsapp_notify_lead_created($id, [
            'company_name' => $company,
            'plan_tier' => $plan,
            'whatsapp' => $whatsapp,
        ]);
    } catch (Throwable $e) {
        error_log('[checkout whatsapp] ' . $e->getMessage());
    }

    echo json_encode([
        'ok' => true,
        'lead_id' => $id,
        'checkout_url' => $checkout,
        'message' => $msg,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    error_log('[checkout_stub] ' . $e->getMessage());
    http_response_code(500);
    $detail = payment_mode() !== 'manual' ? $e->getMessage() : 'Falha ao criar lead.';
    echo json_encode(['ok' => false, 'error' => $detail]);
}
