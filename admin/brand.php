<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';

auth_boot_session();
$user = require_page('brand');

$leadId = lead_admin_request_id();
$leadRow = null;
if ($leadId !== null) {
    require_lead_access($leadId);
    $leadRow = lead_admin_resolve($leadId);
    if (!$leadRow) {
        http_response_code(404);
        admin_header('Lead não encontrado', $user);
        echo '<p class="admin-flash admin-flash--error">Lead não encontrado.</p>';
        admin_footer();
        exit;
    }
    require_once dirname(__DIR__) . '/lib/plans.php';
    $tier = plan_normalize((string) ($leadRow['plan_tier'] ?? 'basic'));
    // Cliente: Marca só no Pro. Staff continua podendo ajustar.
    if (user_is_client($user) && !plan_allows_brand_edit($tier)) {
        http_response_code(403);
        admin_header('Marca indisponível', $user);
        echo '<p class="admin-flash admin-flash--error">O plano Medium não inclui edição de Marca (nome da empresa). Fale com a agência ou faça upgrade para Pro.</p>';
        echo '<p><a href="lead_hub.php?lead_id=' . (int) $leadId . '">← Hub do lead</a></p>';
        admin_footer();
        exit;
    }
    lead_admin_set_context($leadId);
} else {
    $user = require_role_admin();
}

$flash = '';
$error = '';
$defs = settings_definitions();
$values = $leadRow ? lead_admin_settings($leadRow) : settings_all(db());
$groups = ['brand', 'seo', 'analytics'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $input = [];
    foreach ($defs as $key => $def) {
        if (!in_array($def['group'] ?? '', $groups, true)) {
            continue;
        }
        if (($def['type'] ?? '') === 'choice') {
            $input[$key] = isset($_POST[$key]) ? '1' : '0';
        } else {
            $input[$key] = (string) ($_POST[$key] ?? '');
        }
    }
    try {
        if ($leadId) {
            lead_admin_save_settings(db(), $leadId, $input);
            header('Location: brand.php?' . lead_admin_qs($leadId) . '&ok=1');
        } else {
            settings_save_many(db(), $input);
            header('Location: brand.php?ok=1');
        }
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Marca, SEO e analytics salvos.';
    $values = $leadId
        ? lead_admin_settings(lead_admin_resolve($leadId) ?? $leadRow)
        : settings_all(db());
}

$sectionTitles = [
    'brand' => 'Identidade',
    'seo' => 'SEO por página (vazio = usa SEO global)',
    'analytics' => 'Analytics',
];

admin_header('Marca', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Admin' ?></p>
        <h1 class="font-display">Marca</h1>
        <p>Identidade, SEO por página, Analytics.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
        <?php foreach ($groups as $group): ?>
          <h2 class="admin-appearance__label" style="margin-top:<?= $group === 'brand' ? '0' : '1.5rem' ?>;"><?= h($sectionTitles[$group]) ?></h2>
          <?php foreach ($defs as $key => $def): ?>
            <?php if (($def['group'] ?? '') !== $group) continue; ?>
            <?php if (($def['type'] ?? '') === 'choice'): ?>
              <label class="admin-check">
                <input type="checkbox" name="<?= h($key) ?>" value="1" <?= ($values[$key] ?? '0') === '1' ? 'checked' : '' ?>>
                <?= h($def['label']) ?>
              </label>
            <?php else: ?>
              <div class="form-group">
                <label for="<?= h($key) ?>"><?= h($def['label']) ?> <span class="admin-charlimit" data-for="<?= h($key) ?>">0/<?= (int) $def['max'] ?></span></label>
                <?php if (($def['type'] ?? '') === 'text' && (int) $def['max'] > 80): ?>
                  <textarea id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" rows="2" maxlength="<?= (int) $def['max'] ?>"><?= h($values[$key] ?? '') ?></textarea>
                <?php else: ?>
                  <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" maxlength="<?= (int) $def['max'] ?>" value="<?= h($values[$key] ?? '') ?>">
                <?php endif; ?>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar marca</button>
      </form>
      <script src="admin-limits.js"></script>
<?php
admin_footer();
