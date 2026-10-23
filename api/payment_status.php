<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$leadId = isset($_GET['lead_id']) ? (int) $_GET['lead_id'] : 0;
if ($leadId <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'ID de lead inválido.']);
    exit;
}

$crmRoot = dirname(__DIR__, 2) . '/crm_software';
if (!is_file($crmRoot . '/lib/db.php')) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'CRM não localizado.']);
    exit;
}

require_once $crmRoot . '/lib/db.php';
require_once $crmRoot . '/lib/crm/bootstrap.php';
require_once $crmRoot . '/lib/payment/PaymentService.php';

try {
    $pdo = db();
    $lead = crm_lead_get($pdo, $leadId);
    if (!$lead) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Lead não encontrado.']);
        exit;
    }

    $status = (string) ($lead['payment_status'] ?? 'pending');
    if ($status === 'paid') {
        echo json_encode([
            'ok' => true,
            'paid' => true,
            'status' => 'CONFIRMED',
            'lead_id' => $leadId,
        ]);
        exit;
    }

    if ($status === 'overdue' || $status === 'failed') {
        echo json_encode([
            'ok' => true,
            'paid' => false,
            'status' => 'OVERDUE',
            'lead_id' => $leadId,
        ]);
        exit;
    }

    // Se estiver pendente e em modo live com Asaas, consulta o status da cobrança
    $cfg = payment_config();
    if ($cfg['mode'] === 'live' && !empty($cfg['api_key'])) {
        $stmt = $pdo->prepare('SELECT provider_charge_id FROM payment_charges WHERE lead_id = :lid ORDER BY id DESC LIMIT 1');
        $stmt->execute([':lid' => $leadId]);
        $chargeId = (string) ($stmt->fetchColumn() ?: '');

        if ($chargeId !== '') {
            $base = $cfg['sandbox'] ? 'https://sandbox.asaas.com/api/v3' : 'https://api.asaas.com/v3';
            $ch = curl_init($base . '/payments/' . urlencode($chargeId));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'access_token: ' . $cfg['api_key'],
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => 4,
            ]);
            $res = curl_exec($ch);
            curl_close($ch);
            $pay = json_decode((string) $res, true);

            $asaasStatus = (string) ($pay['status'] ?? '');
            if (in_array($asaasStatus, ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'], true)) {
                payment_apply_paid($pdo, $leadId, (string) ($lead['plan_tier'] ?? 'basic'), 'asaas_poll');
                echo json_encode([
                    'ok' => true,
                    'paid' => true,
                    'status' => 'CONFIRMED',
                    'lead_id' => $leadId,
                ]);
                exit;
            } elseif (in_array($asaasStatus, ['OVERDUE', 'REFUNDED'], true)) {
                echo json_encode([
                    'ok' => true,
                    'paid' => false,
                    'status' => $asaasStatus,
                    'lead_id' => $leadId,
                ]);
                exit;
            }
        }
    }

    echo json_encode([
        'ok' => true,
        'paid' => false,
        'status' => 'PENDING',
        'lead_id' => $leadId,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Falha interna ao verificar pagamento.']);
}
