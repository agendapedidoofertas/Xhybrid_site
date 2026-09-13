<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';

auth_boot_session();

if (user_count() === 0) {
    header('Location: setup.php');
    exit;
}

$user = require_page('index');

$leadId = lead_admin_request_id();
if ($leadId !== null) {
    // Compat: antigo Painel/Imagens com ?lead_id= → listagem de imagens
    header('Location: images.php?' . lead_admin_qs($leadId));
    exit;
}

if (user_is_client($user)) {
    $own = user_crm_lead_id($user);
    if ($own) {
        header('Location: lead_hub.php?lead_id=' . $own);
        exit;
    }
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
