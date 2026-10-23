<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();
require_page('webhooks');

$crmDbPath = dirname(__DIR__, 2) . '/crm_software/data/crm.sqlite';
$logs = [];

if (is_file($crmDbPath)) {
    try {
        $crmPdo = new PDO('sqlite:' . $crmDbPath);
        $crmPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $stmt = $crmPdo->query('SELECT id, provider, event_type as event, payload_json as payload, processed_at as created_at FROM payment_webhook_events ORDER BY id DESC LIMIT 20');
        if ($stmt) {
            $logs = $stmt->fetchAll();
        }
    } catch (Throwable $_) {}
}

if (empty($logs)) {
    $pdo = db();
    try {
        $stmt = $pdo->query('SELECT id, provider, event, payload, created_at FROM webhook_logs ORDER BY id DESC LIMIT 20');
        if ($stmt) {
            $logs = $stmt->fetchAll();
        }
    } catch (Throwable $_) {}
}

admin_header('Auditoria de Webhooks');
?>
      <header class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;">
        <div>
          <p class="eyebrow">Agência · Integração</p>
          <h1 class="font-display">Webhooks Asaas</h1>
          <p>Últimos 20 eventos recebidos do gateway de faturamento.</p>
        </div>
        <div>
          <a href="index.php" class="btn btn-outline">← Voltar ao Painel</a>
        </div>
      </header>

      <?php if (empty($logs)): ?>
        <div style="background:#141414;border:1px solid #262626;border-radius:0.75rem;padding:2.5rem;text-align:center;color:#a3a3a3;margin-top:1.5rem;">
          <p style="font-size:1.1rem;margin:0 0 0.5rem;color:#f5f5f5;">Nenhum webhook registrado ainda.</p>
          <p style="font-size:0.9rem;margin:0;">Assim que o Asaas disparar notificações de pagamento (PIX ou Cartão), elas aparecerão aqui automaticamente.</p>
        </div>
      <?php else: ?>
        <div style="margin-top:1.5rem;overflow-x:auto;background:#141414;border:1px solid #262626;border-radius:0.75rem;">
          <table style="width:100%;border-collapse:collapse;font-size:0.9rem;text-align:left;">
            <thead>
              <tr style="border-bottom:1px solid #262626;background:#1a1a1a;color:#a3a3a3;">
                <th style="padding:0.75rem 1rem;">ID</th>
                <th style="padding:0.75rem 1rem;">Data/Hora</th>
                <th style="padding:0.75rem 1rem;">Provedor</th>
                <th style="padding:0.75rem 1rem;">Evento</th>
                <th style="padding:0.75rem 1rem;">Lead / Fatura</th>
                <th style="padding:0.75rem 1rem;">Status</th>
                <th style="padding:0.75rem 1rem;">Payload</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $l): ?>
                <?php
                $rawPayload = (string) ($l['payload'] ?? '');
                $parsed = json_decode($rawPayload, true);
                $event = (string) ($l['event'] ?? ($parsed['event'] ?? ''));
                $leadRef = '';
                $valStr = '';

                if (is_array($parsed)) {
                    $payment = $parsed['payment'] ?? [];
                    if (!empty($payment['externalReference'])) {
                        $leadRef = $payment['externalReference'];
                    }
                    if (!empty($payment['value'])) {
                        $valStr = 'R$ ' . number_format((float) $payment['value'], 2, ',', '.');
                    }
                }

                $isConfirmed = str_contains($event, 'CONFIRMED') || str_contains($event, 'RECEIVED');
                $isOverdue = str_contains($event, 'OVERDUE') || str_contains($event, 'REFUNDED') || str_contains($event, 'FAILED');
                $statusColor = $isConfirmed ? '#22c55e' : ($isOverdue ? '#ef4444' : '#eab308');
                $statusBg = $isConfirmed ? 'rgba(34,197,94,0.15)' : ($isOverdue ? 'rgba(239,68,68,0.15)' : 'rgba(234,179,8,0.15)');
                $statusLabel = $isConfirmed ? 'CONFIRMADO' : ($isOverdue ? 'VENCIDO/RECUSADO' : 'PENDENTE');
                ?>
                <tr style="border-bottom:1px solid #222;">
                  <td style="padding:0.75rem 1rem;color:#737373;">#<?= (int) $l['id'] ?></td>
                  <td style="padding:0.75rem 1rem;white-space:nowrap;"><?= h(substr((string) ($l['created_at'] ?? ''), 0, 19)) ?></td>
                  <td style="padding:0.75rem 1rem;text-transform:uppercase;font-weight:600;color:#a3a3a3;"><?= h((string) ($l['provider'] ?? 'asaas')) ?></td>
                  <td style="padding:0.75rem 1rem;font-weight:600;color:#f5f5f5;"><code><?= h($event) ?></code></td>
                  <td style="padding:0.75rem 1rem;">
                    <?php if ($leadRef !== ''): ?>
                      <span style="color:#60a5fa;"><?= h($leadRef) ?></span>
                    <?php endif; ?>
                    <?php if ($valStr !== ''): ?>
                      <span style="display:block;font-size:0.8rem;color:#a3a3a3;"><?= h($valStr) ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="padding:0.75rem 1rem;">
                    <span style="display:inline-block;padding:0.2rem 0.55rem;border-radius:0.35rem;font-size:0.75rem;font-weight:700;color:<?= $statusColor ?>;background:<?= $statusBg ?>;">
                      <?= $statusLabel ?>
                    </span>
                  </td>
                  <td style="padding:0.75rem 1rem;">
                    <details>
                      <summary style="cursor:pointer;color:#60a5fa;font-size:0.85rem;">Ver JSON</summary>
                      <pre style="margin-top:0.5rem;padding:0.75rem;background:#0d0d0d;border:1px solid #333;border-radius:0.5rem;max-height:14rem;overflow:auto;font-size:0.75rem;color:#d4d4d4;"><?= h($rawPayload) ?></pre>
                    </details>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
<?php
admin_footer();
