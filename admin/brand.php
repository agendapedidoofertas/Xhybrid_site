<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';

auth_boot_session();
$user = require_role_admin();

$flash = '';
$error = '';
$defs = settings_definitions();
$values = settings_all(db());
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
        settings_save_many(db(), $input);
        header('Location: brand.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Marca, SEO e analytics salvos.';
    $values = settings_all(db());
}

$sectionTitles = [
    'brand' => 'Identidade',
    'seo' => 'SEO por página (vazio = usa SEO global)',
    'analytics' => 'Analytics',
];

admin_header('Marca', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Admin</p>
        <h1 class="font-display">Marca</h1>
        <p>Identidade, SEO por página, Analytics. Logo/favicon: slugs <code>logo</code> e <code>favicon</code> em Imagens.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
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
