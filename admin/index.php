<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();
$user = require_admin();

if (user_is_client($user)) {
    $own = user_crm_lead_id($user);
    if ($own) {
        header('Location: lead_hub.php?lead_id=' . $own);
        exit;
    }
}

$recentPaid = [];
$crmDbPath = dirname(__DIR__, 2) . '/crm_software/data/crm.sqlite';
if (is_file($crmDbPath)) {
    try {
        $crmPdo = new PDO('sqlite:' . $crmDbPath);
        $crmPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $stmt = $crmPdo->query("SELECT id, company_name, plan_tier, updated_at FROM leads WHERE payment_status = 'paid' ORDER BY updated_at DESC LIMIT 3");
        if ($stmt) {
            $recentPaid = $stmt->fetchAll();
        }
    } catch (Throwable $_) {}
}

$links = admin_agency_hub_links($user);

admin_header('Painel', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Agência</p>
        <h1 class="font-display">Painel</h1>
        <p>Escolha uma área para editar a vitrine Xhybrid.</p>
        <p class="text-muted roboto-hub-hint">Alguma dúvida? Pergunte ao Roboto.</p>
      </header>

      <?php if (!empty($recentPaid)): ?>
        <div style="margin:1.5rem 0;padding:1rem 1.25rem;background:#142417;border:1px solid #22c55e;border-radius:0.75rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:0.75rem;">
          <div style="display:flex;align-items:center;gap:0.75rem;">
            <span style="font-size:1.4rem;">💰</span>
            <div>
              <p style="margin:0;font-weight:700;color:#4ade80;">Pagamento Confirmado Recentemente!</p>
              <p style="margin:0;font-size:0.85rem;color:#d4d4d4;">
                Lead #<?= (int) $recentPaid[0]['id'] ?> (<?= h((string) ($recentPaid[0]['company_name'] ?? 'Cliente')) ?>) · Plano <?= strtoupper((string) ($recentPaid[0]['plan_tier'] ?? 'BASIC')) ?>
              </p>
            </div>
          </div>
          <div style="display:flex;gap:0.5rem;">
            <a href="leads.php?lead_id=<?= (int) $recentPaid[0]['id'] ?>" class="btn btn-primary" style="font-size:0.85rem;padding:0.4rem 0.85rem;background:#22c55e;color:#052e16;">Ver Lead</a>
            <a href="webhooks.php" class="btn btn-outline" style="font-size:0.85rem;padding:0.4rem 0.85rem;">Auditoria Webhooks</a>
          </div>
        </div>
      <?php endif; ?>

      <div class="admin-hub-grid">
        <?php foreach ($links as $link): ?>
          <?php if (!$link['show']) {
              continue;
          } ?>
          <a class="admin-hub-card" href="<?= h($link['href']) ?>">
            <?= admin_hub_icon($link['icon']) ?>
            <span class="admin-hub-card__body">
              <h2 class="font-display"><?= h($link['label']) ?></h2>
              <p class="text-muted"><?= h($link['desc']) ?></p>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
<?php
admin_footer();
