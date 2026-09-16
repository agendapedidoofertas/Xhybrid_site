<?php

declare(strict_types=1);

/**
 * Área mínima "Minha assinatura" para client_medium / client_pro.
 */

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';
require_once dirname(__DIR__) . '/lib/crm_bridge.php';

auth_boot_session();
$user = require_login();
if (!user_is_client($user)) {
    header('Location: index.php');
    exit;
}

$leadId = (int) ($user['crm_lead_id'] ?? 0);
$row = $leadId > 0 ? published_site_get_by_lead(db(), $leadId) : null;
$crmLead = null;
$checkout = '';
if ($leadId > 0 && crm_bridge_configured()) {
    try {
        $crm = crm_bridge_pdo();
        $st = $crm->prepare('SELECT * FROM leads WHERE id = :id LIMIT 1');
        $st->execute([':id' => $leadId]);
        $crmLead = $st->fetch() ?: null;
        $ch = $crm->prepare(
            "SELECT checkout_url FROM payment_charges WHERE lead_id = :id AND status = 'pending' ORDER BY id DESC LIMIT 1"
        );
        $ch->execute([':id' => $leadId]);
        $checkout = (string) ($ch->fetchColumn() ?: '');
    } catch (Throwable $e) {
        // ignore
    }
}

$path = $row ? published_site_public_path($row) : '';
$host = $row ? (string) ($row['public_host'] ?? '') : '';
$status = $crmLead ? (string) ($crmLead['payment_status'] ?? '') : '';
$plan = $crmLead ? (string) ($crmLead['plan_tier'] ?? '') : (string) ($row['plan_tier'] ?? '');

admin_header('Minha assinatura', $user);
?>
<header class="page-header" style="padding-top:1rem;">
  <p class="eyebrow">Conta</p>
  <h1 class="font-display">Minha assinatura</h1>
</header>
<dl class="detail" style="max-width:36rem;">
  <div><dt>Plano</dt><dd><?= h($plan ?: '—') ?></dd></div>
  <div><dt>Status pagamento</dt><dd><?= h($status ?: '—') ?></dd></div>
  <div><dt>Host</dt><dd><?= h($host ?: '—') ?></dd></div>
  <div><dt>Path</dt><dd><?= $path !== '' ? '<code>' . h($path) . '</code>' : '—' ?></dd></div>
</dl>
<?php if ($checkout !== ''): ?>
<p><a class="btn btn-primary" href="<?= h($checkout) ?>" target="_blank" rel="noopener">2ª via / pagar</a></p>
<?php endif; ?>
<p class="text-muted">Upgrade de plano: fale com a agência ou use o checkout público quando disponível.</p>
<?php admin_footer();
