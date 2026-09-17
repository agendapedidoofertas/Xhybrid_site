<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';

auth_boot_session();
$user = require_page('contact');
$isAdmin = user_is_admin($user);

$leadId = lead_admin_request_id();
$leadRow = null;
if ($leadId !== null) {
    require_lead_access($leadId);
    $leadRow = lead_admin_resolve($leadId);
    if (!$leadRow) {
        http_response_code(404);
        admin_header('Lead não encontrado', $user);
        echo '<p class="admin-flash admin-flash--error">Lead não encontrado. <a href="leads.php">Voltar</a></p>';
        admin_footer();
        exit;
    }
    lead_admin_set_context($leadId);
}

$flash = '';
$error = '';
$defs = settings_definitions();
$values = $leadRow ? lead_admin_settings($leadRow) : settings_all(db());
// SMTP permanece no backend (settings/api); UI oculta — contato via WhatsApp.
$groups = ['contact'];
$redirBase = 'contact.php' . ($leadId ? '?' . lead_admin_qs($leadId) : '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $input = [];
    foreach ($defs as $key => $def) {
        if (!in_array($def['group'] ?? '', $groups, true)) {
            continue;
        }
        $input[$key] = (string) ($_POST[$key] ?? '');
    }
    try {
        if ($leadId) {
            lead_admin_save_settings(db(), $leadId, $input);
            header('Location: contact.php?' . lead_admin_qs($leadId) . '&ok=1');
        } else {
            settings_save_many(db(), $input);
            header('Location: contact.php?ok=1');
        }
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        foreach ($input as $k => $v) {
            $values[$k] = settings_sanitize($k, $v);
        }
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Contato salvo.';
    $values = $leadRow ? lead_admin_settings(lead_admin_resolve($leadId) ?? $leadRow) : settings_all(db());
}

admin_header('Contato', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Site' ?></p>
        <h1 class="font-display">Contato</h1>
        <p><?= $leadId
            ? 'Canais do site deste lead. Campo vazio esconde o bloco no site. Formulário público usa WhatsApp.'
            : 'Canais do site (WhatsApp, e-mail, redes, endereço). Campo vazio esconde o bloco no site. O formulário público prioriza WhatsApp.' ?></p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
        <h2 class="admin-appearance__label">Canais</h2>
        <?php foreach ($defs as $key => $def): ?>
          <?php if (($def['group'] ?? '') !== 'contact') continue; ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($def['label']) ?> <span class="admin-charlimit" data-for="<?= h($key) ?>">0/<?= (int) $def['max'] ?></span></label>
            <?php if (($def['type'] ?? '') === 'text' && (int) $def['max'] > 80): ?>
              <textarea id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" rows="2" maxlength="<?= (int) $def['max'] ?>"><?= h($values[$key] ?? '') ?></textarea>
            <?php else: ?>
              <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" maxlength="<?= (int) $def['max'] ?>" value="<?= h($values[$key] ?? '') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar contato</button>
      </form>
      <script src="admin-limits.js"></script>
<?php
admin_footer();
