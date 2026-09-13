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

$links = [
    ['contact.php', 'Contato', 'Editar contato da vitrine', user_can_page($user, 'contact')],
    ['texts.php', 'Textos', 'Editar textos da vitrine', user_can_page($user, 'texts')],
    ['images.php', 'Imagens', 'Logo, favicon, hero e about', user_can_page($user, 'index')],
    ['services.php', 'Serviços', 'Catálogo de serviços', user_can_page($user, 'services')],
    ['appearance.php', 'Aparência', 'Tema, fonte e layout', user_can_page($user, 'appearance')],
    ['preset.php', 'Preset', 'Aplicar preset de nicho', user_can_page($user, 'preset')],
    ['brand.php', 'Marca', 'Nome e identidade', user_can_page($user, 'brand')],
    ['plan.php', 'Plano', 'Plano e recursos', user_can_page($user, 'plan')],
    ['sections.php', 'Visibilidade', 'Seções do site', user_can_page($user, 'sections')],
    ['leads.php', 'Leads', 'Sites publicados do CRM', user_is_staff($user) && user_can_page($user, 'leads')],
    ['backup.php', 'Backup', 'Exportar e restaurar', user_is_admin($user)],
    ['users.php', 'Usuários', 'Contas e permissões', user_is_admin($user)],
    ['password.php', 'Senha', 'Alterar sua senha', true],
];

admin_header('Painel', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Agência</p>
        <h1 class="font-display">Painel</h1>
        <p>Escolha uma área para editar a vitrine Xhybrid.</p>
      </header>

      <div class="admin-hub-grid">
        <?php foreach ($links as [$href, $label, $desc, $show]): ?>
          <?php if (!$show) {
              continue;
          } ?>
          <a class="admin-hub-card" href="<?= h($href) ?>">
            <h2 class="font-display"><?= h($label) ?></h2>
            <p class="text-muted"><?= h($desc) ?></p>
          </a>
        <?php endforeach; ?>
      </div>
<?php
admin_footer();
