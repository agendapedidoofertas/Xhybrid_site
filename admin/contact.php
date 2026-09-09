<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';

auth_boot_session();
$user = require_admin();

$flash = '';
$error = '';
$defs = settings_definitions();
$values = settings_all(db());
$groups = ['contact', 'smtp'];

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
        settings_save_many(db(), $input);
        header('Location: contact.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        foreach ($input as $k => $v) {
            $values[$k] = settings_sanitize($k, $v);
        }
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Contato e SMTP salvos.';
    $values = settings_all(db());
}

admin_header('Contato', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Site</p>
        <h1 class="font-display">Contato</h1>
        <p>Canais do site e SMTP para o formulário enviar e-mail de verdade (sem gravar mensagens no SQLite).</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <h2 class="admin-appearance__label">Canais</h2>
        <?php foreach ($defs as $key => $def): ?>
          <?php if (($def['group'] ?? '') !== 'contact') continue; ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($def['label']) ?> <span class="admin-charlimit" data-for="<?= h($key) ?>">0/<?= (int) $def['max'] ?></span></label>
            <?php if (($def['type'] ?? '') === 'text' && (int) $def['max'] > 80): ?>
              <textarea id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" rows="2" maxlength="<?= (int) $def['max'] ?>"><?= h($values[$key] ?? '') ?></textarea>
            <?php else: ?>
              <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" maxlength="<?= (int) $def['max'] ?>" value="<?= h($values[$key] ?? '') ?>"<?= $key === 'smtp_pass' || str_contains($key, 'pass') ? '' : '' ?>>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <h2 class="admin-appearance__label" style="margin-top:1.5rem;">SMTP (formulário)</h2>
        <p class="text-muted" style="margin:0 0 0.75rem;font-size:0.875rem;">Preencha host, usuário e senha. Destino vazio usa o e-mail do site. A senha fica no SQLite — use conta SMTP dedicada.</p>
        <?php foreach ($defs as $key => $def): ?>
          <?php if (($def['group'] ?? '') !== 'smtp') continue; ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($def['label']) ?></label>
            <input
              id="<?= h($key) ?>"
              name="<?= h($key) ?>"
              class="form-input"
              maxlength="<?= (int) $def['max'] ?>"
              value="<?= h($values[$key] ?? '') ?>"
              <?= $key === 'smtp_pass' ? 'type="password" autocomplete="new-password"' : 'type="text"' ?>
            >
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar contato</button>
      </form>
      <script src="admin-limits.js"></script>
<?php
admin_footer();
