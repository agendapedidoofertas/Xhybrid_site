<?php

declare(strict_types=1);

require_once __DIR__ . '/crm_bridge.php';
require_once __DIR__ . '/published_sites.php';

const PAYMENT_GRACE_HOURS = 72;

/**
 * Garante colunas de grace/claim no CRM.
 */
function payment_crm_migrate(PDO $crm): void
{
    $cols = $crm->query('PRAGMA table_info(leads)')->fetchAll(PDO::FETCH_ASSOC);
    $names = array_map(static fn ($c) => (string) ($c['name'] ?? ''), $cols);
    $add = [
        'payment_grace_until' => "TEXT NOT NULL DEFAULT ''",
        'payment_self_claims' => 'INTEGER NOT NULL DEFAULT 0',
        'payment_self_blocked' => 'INTEGER NOT NULL DEFAULT 0',
    ];
    foreach ($add as $col => $def) {
        if (!in_array($col, $names, true)) {
            $crm->exec("ALTER TABLE leads ADD COLUMN {$col} {$def}");
        }
    }
}

/**
 * @return array<string, mixed>|null
 */
function payment_crm_lead(PDO $crm, int $leadId): ?array
{
    payment_crm_migrate($crm);
    $st = $crm->prepare('SELECT * FROM leads WHERE id = :id LIMIT 1');
    $st->execute([':id' => $leadId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function payment_grace_expired(?string $graceUntil): bool
{
    $g = trim((string) $graceUntil);
    if ($g === '') {
        return true;
    }
    $ts = strtotime($g);
    if ($ts === false) {
        return true;
    }
    return $ts <= time();
}

/**
 * Expira grace: desativa site no CRM + Xhybrid se pending_confirm passou de 72h.
 *
 * @return bool true se desativou algo
 */
function payment_expire_if_needed(PDO $xhybrid, int $crmLeadId): bool
{
    if ($crmLeadId <= 0 || !crm_bridge_configured()) {
        return false;
    }
    try {
        $crm = crm_bridge_pdo();
    } catch (Throwable $e) {
        return false;
    }
    $lead = payment_crm_lead($crm, $crmLeadId);
    if (!$lead) {
        return false;
    }
    $status = strtolower(trim((string) ($lead['payment_status'] ?? '')));
    if ($status !== 'pending_confirm') {
        return false;
    }
    if (!payment_grace_expired((string) ($lead['payment_grace_until'] ?? ''))) {
        return false;
    }

    $now = gmdate('c');
    $crm->prepare(
        "UPDATE leads SET site_active = 0, payment_status = 'overdue', payment_grace_until = '', updated_at = :u WHERE id = :id"
    )->execute([':u' => $now, ':id' => $crmLeadId]);

    $xhybrid->prepare(
        'UPDATE published_sites SET site_active = 0, updated_at = :u WHERE crm_lead_id = :id'
    )->execute([':u' => $now, ':id' => $crmLeadId]);

    return true;
}

/**
 * Cliente declara pagamento (1Ã‚Âª vez).
 *
 * @return array{ok:bool,code:string,message:string,grace_until?:string,whatsapp?:string}
 */
function payment_claim_self(PDO $xhybrid, int $crmLeadId, string $slug, string $letter): array
{
    require_once __DIR__ . '/pix.php';
    $cfg = pix_config();
    $wa = $cfg['support_whatsapp'];

    if (!crm_bridge_configured()) {
        return [
            'ok' => false,
            'code' => 'no_crm',
            'message' => 'NÃƒÂ£o foi possÃƒÂ­vel validar o pagamento agora. Fale no WhatsApp.',
            'whatsapp' => $wa,
        ];
    }

    $row = published_site_find_public($xhybrid, $slug, $crmLeadId, $letter);
    if (!$row) {
        return ['ok' => false, 'code' => 'not_found', 'message' => 'Site nÃƒÂ£o encontrado.'];
    }

    payment_expire_if_needed($xhybrid, $crmLeadId);

    try {
        $crm = crm_bridge_pdo();
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'code' => 'no_crm',
            'message' => 'CRM indisponÃƒÂ­vel. Fale no WhatsApp.',
            'whatsapp' => $wa,
        ];
    }

    $lead = payment_crm_lead($crm, $crmLeadId);
    if (!$lead) {
        return ['ok' => false, 'code' => 'not_found', 'message' => 'Lead nÃƒÂ£o encontrado no CRM.'];
    }

    $status = strtolower(trim((string) ($lead['payment_status'] ?? 'pending')));
    $claims = (int) ($lead['payment_self_claims'] ?? 0);
    $blocked = (int) ($lead['payment_self_blocked'] ?? 0) === 1;
    $grace = (string) ($lead['payment_grace_until'] ?? '');

    if ($status === 'paid') {
        // Garante ativo
        $now = gmdate('c');
        $crm->prepare('UPDATE leads SET site_active = 1, payment_self_blocked = 0, payment_grace_until = \'\', updated_at = :u WHERE id = :id')
            ->execute([':u' => $now, ':id' => $crmLeadId]);
        $xhybrid->prepare('UPDATE published_sites SET site_active = 1, updated_at = :u WHERE crm_lead_id = :id')
            ->execute([':u' => $now, ':id' => $crmLeadId]);
        return [
            'ok' => true,
            'code' => 'already_paid',
            'message' => 'Pagamento jÃƒÂ¡ confirmado. Recarregando o siteÃ¢â‚¬Â¦',
        ];
    }

    if ($blocked || $claims >= 2) {
        return [
            'ok' => false,
            'code' => 'need_whatsapp',
            'message' => 'A reativaÃƒÂ§ÃƒÂ£o automÃƒÂ¡tica jÃƒÂ¡ foi usada. Envie mensagem no WhatsApp para o comercial liberar o site.',
            'whatsapp' => $wa,
        ];
    }

    if ($status === 'pending_confirm' && !payment_grace_expired($grace)) {
        return [
            'ok' => true,
            'code' => 'already_pending',
            'message' => 'Site jÃƒÂ¡ reativado provisoriamente. Aguarde a confirmaÃƒÂ§ÃƒÂ£o do pagamento (atÃƒÂ© 72h).',
            'grace_until' => $grace,
        ];
    }

    // JÃƒÂ¡ usou a 1Ã‚Âª tentativa (claims >= 1) e nÃƒÂ£o estÃƒÂ¡ pago Ã¢â€ â€™ bloqueia
    if ($claims >= 1) {
        $now = gmdate('c');
        $crm->prepare(
            'UPDATE leads SET payment_self_blocked = 1, payment_self_claims = :c, site_active = 0, payment_status = \'overdue\', payment_grace_until = \'\', updated_at = :u WHERE id = :id'
        )->execute([':c' => max($claims, 2), ':u' => $now, ':id' => $crmLeadId]);
        $xhybrid->prepare('UPDATE published_sites SET site_active = 0, updated_at = :u WHERE crm_lead_id = :id')
            ->execute([':u' => $now, ':id' => $crmLeadId]);
        return [
            'ok' => false,
            'code' => 'need_whatsapp',
            'message' => 'Segunda tentativa sem confirmaÃƒÂ§ÃƒÂ£o de pagamento. Fale no WhatsApp Ã¢â‚¬â€ sÃƒÂ³ o admin libera o site.',
            'whatsapp' => $wa,
        ];
    }

    // 1Ã‚Âª reivindicaÃƒÂ§ÃƒÂ£o
    $now = gmdate('c');
    $graceUntil = gmdate('c', time() + PAYMENT_GRACE_HOURS * 3600);
    $crm->prepare(
        "UPDATE leads SET
            site_active = 1,
            payment_status = 'pending_confirm',
            payment_grace_until = :g,
            payment_self_claims = 1,
            payment_self_blocked = 0,
            updated_at = :u
         WHERE id = :id"
    )->execute([':g' => $graceUntil, ':u' => $now, ':id' => $crmLeadId]);

    $xhybrid->prepare(
        'UPDATE published_sites SET site_active = 1, updated_at = :u WHERE crm_lead_id = :id'
    )->execute([':u' => $now, ':id' => $crmLeadId]);

    return [
        'ok' => true,
        'code' => 'claimed',
        'message' => 'Site reativado por 72 horas. O pagamento serÃƒÂ¡ conferido no CRM; se nÃƒÂ£o confirmar, o site desativa de novo.',
        'grace_until' => $graceUntil,
    ];
}

/**
 * Estado para a pÃƒÂ¡gina inativa.
 *
 * @return array<string, mixed>
 */
function payment_inactive_context(PDO $xhybrid, array $publishedRow): array
{
    require_once __DIR__ . '/pix.php';
    $leadId = (int) ($publishedRow['crm_lead_id'] ?? 0);
    $slug = (string) ($publishedRow['slug'] ?? '');
    $code = (string) ($publishedRow['url_code'] ?? '');
    $company = (string) ($publishedRow['company_name'] ?? '');

    payment_expire_if_needed($xhybrid, $leadId);

    $cfg = pix_config();
    $txid = $cfg['txid_prefix'] . $leadId;
    $payload = pix_payload(['txid' => $txid]);

    $state = 'can_claim';
    $graceUntil = '';
    $claims = 0;
    $blocked = false;
    $payStatus = 'pending';

    if (crm_bridge_configured()) {
        try {
            $lead = payment_crm_lead(crm_bridge_pdo(), $leadId);
            if ($lead) {
                $payStatus = strtolower(trim((string) ($lead['payment_status'] ?? 'pending')));
                $claims = (int) ($lead['payment_self_claims'] ?? 0);
                $blocked = (int) ($lead['payment_self_blocked'] ?? 0) === 1;
                $graceUntil = (string) ($lead['payment_grace_until'] ?? '');
                if ($blocked || $claims >= 2) {
                    $state = 'need_whatsapp';
                } elseif ($payStatus === 'pending_confirm' && !payment_grace_expired($graceUntil)) {
                    $state = 'pending_confirm';
                } elseif ($claims >= 1) {
                    $state = 'need_whatsapp';
                }
            }
        } catch (Throwable $e) {
            // keep defaults
        }
    }

    $waMsg = 'Olá! Sou o lead #' . $leadId . ' (' . $company . '). Gostaria de regularizar o pagamento do site.';

    $gwConfig = [];
    if (crm_bridge_configured()) {
        $gwFile = crm_bridge_root() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'payment_gateway.php';
        if (is_file($gwFile)) {
            $gwLoaded = include $gwFile;
            if (is_array($gwLoaded)) {
                $gwConfig = $gwLoaded;
            }
        }
    }
    $paymentMode = getenv('PAYMENT_MODE') ?: ($gwConfig['mode'] ?? 'live');
    $paymentMode = is_string($paymentMode) && trim($paymentMode) !== '' ? strtolower(trim($paymentMode)) : 'live';
    $checkoutUrl = '';

    if (crm_bridge_configured()) {
        try {
            $crm = crm_bridge_pdo();
            // Busca cobrança pendente do Asaas/gateway
            $ch = $crm->prepare(
                "SELECT checkout_url FROM payment_charges WHERE lead_id = :id AND status = 'pending' AND checkout_url LIKE 'http%' ORDER BY id DESC LIMIT 1"
            );
            $ch->execute([':id' => $leadId]);
            $checkoutUrl = (string) ($ch->fetchColumn() ?: '');

            // Se não tiver pendente, busca a última URL de cobrança válida para este lead
            if ($checkoutUrl === '') {
                $ch2 = $crm->prepare(
                    "SELECT checkout_url FROM payment_charges WHERE lead_id = :id AND checkout_url LIKE 'http%' ORDER BY id DESC LIMIT 1"
                );
                $ch2->execute([':id' => $leadId]);
                $checkoutUrl = (string) ($ch2->fetchColumn() ?: '');
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    // Se ainda não existir fatura Asaas gerada, direciona para o checkout direto do plano
    if ($checkoutUrl === '') {
        $planTier = strtolower(trim((string) ($publishedRow['plan_tier'] ?? 'basic')));
        $checkoutUrl = '/checkout.html?lead_id=' . $leadId . '&plan=' . urlencode($planTier) . '&terms=1';
    }

    return [
        'lead_id' => $leadId,
        'slug' => $slug,
        'code' => $code,
        'company' => $company,
        'plan_tier' => (string) ($publishedRow['plan_tier'] ?? 'basic'),
        'state' => $state,
        'payment_status' => $payStatus,
        'claims' => $claims,
        'grace_until' => $graceUntil,
        'pix_payload' => $paymentMode === 'manual' ? $payload : '',
        'pix_qr_url' => $paymentMode === 'manual' ? pix_qr_image_url($payload, 240) : '',
        'pix_key' => $paymentMode === 'manual' ? $cfg['key'] : '',
        'payment_mode' => $paymentMode,
        'checkout_url' => $checkoutUrl,
        'allow_claim' => false,
        'support_whatsapp' => $cfg['support_whatsapp'],
        'whatsapp_url' => $cfg['support_whatsapp'] !== ''
            ? 'https://wa.me/' . preg_replace('/\D+/', '', $cfg['support_whatsapp']) . '?text=' . rawurlencode($waMsg)
            : '',
    ];
}
