<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';

auth_boot_session();
$user = require_admin();

$leadId = lead_admin_request_id();
if ($leadId === null) {
    header('Location: leads.php');
    exit;
}

require_lead_access($leadId);
$row = lead_admin_resolve($leadId);
if (!$row) {
    http_response_code(404);
    admin_header('Lead não encontrado', $user);
    echo '<p class="admin-flash admin-flash--error">Site do lead #' . (int) $leadId . ' não encontrado. <a href="leads.php">Voltar</a></p>';
    admin_footer();
    exit;
}

lead_admin_set_context($leadId);
$path = published_site_public_path($row);
$active = (int) ($row['site_active'] ?? 0) === 1;
$qs = lead_admin_qs($leadId);
$plan = (string) ($row['plan_tier'] ?? 'basic');

$links = [
    ['contact.php', 'Contato', true],
    ['texts.php', 'Textos', true],
    ['appearance.php', 'Aparência', user_can_page($user, 'appearance')],
    ['preset.php', 'Preset', user_can_page($user, 'preset')],
    ['brand.php', 'Marca', user_can_page($user, 'brand')],
    ['plan.php', 'Plano', user_can_page($user, 'plan')],
    ['sections.php', 'Visibilidade', user_can_page($user, 'sections')],
    ['services.php', 'Serviços', user_can_page($user, 'services')],
    ['images.php', 'Imagens', true],
    ['lead_site.php', 'Resumo rápido', true],
];

admin_header('Lead #' . $leadId, $user);
$backLeads = user_is_staff($user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">
          <?php if ($backLeads): ?>
            <a href="leads.php">← Sites de leads</a>
          <?php else: ?>
            Hub do lead
          <?php endif; ?>
        </p>
        <h1 class="font-display"><?= h((string) $row['company_name']) ?></h1>
        <p>
          Lead #<?= (int) $leadId ?>
          · Plano <?= h($plan) ?>
          · <?= $active ? 'Ativo' : 'Inativo' ?>
          <?php if ($path !== ''): ?>
            · <a href="<?= h($path) ?>" target="_blank" rel="noopener"><?= h($path) ?></a>
          <?php endif; ?>
        </p>
      </header>

      <div class="admin-hub-grid">
        <?php foreach ($links as [$href, $label, $show]): ?>
          <?php if (!$show) {
              continue;
          } ?>
          <a class="admin-hub-card" href="<?= h($href . '?' . $qs) ?>">
            <h2 class="font-display"><?= h($label) ?></h2>
            <p class="text-muted">Editar <?= h(strtolower($label)) ?> deste lead</p>
          </a>
        <?php endforeach; ?>
      </div>
<?php
admin_footer();
