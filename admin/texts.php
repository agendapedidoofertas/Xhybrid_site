<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';

auth_boot_session();
$user = require_admin();

$allowedTabs = ['menu', 'home', 'sobre', 'galeria', 'contato_page', 'footer'];
$tab = (string) ($_GET['tab'] ?? 'menu');
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'menu';
}

$flash = '';
$error = '';
$defs = settings_definitions();
$groups = settings_groups();
$values = settings_all(db());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $postTab = (string) ($_POST['tab'] ?? $tab);
    if (!in_array($postTab, $allowedTabs, true)) {
        $postTab = 'menu';
    }
    $tab = $postTab;
    $input = [];
    foreach ($defs as $key => $def) {
        if (($def['group'] ?? '') !== $tab) {
            continue;
        }
        $input[$key] = (string) ($_POST[$key] ?? '');
    }
    try {
        settings_save_many(db(), $input);
        header('Location: texts.php?tab=' . urlencode($tab) . '&ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        foreach ($input as $k => $v) {
            $values[$k] = settings_sanitize($k, $v);
        }
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Textos salvos.';
}

$tabLabels = [
    'menu' => 'Menu',
    'home' => 'Home',
    'sobre' => 'Sobre',
    'galeria' => 'Galeria',
    'contato_page' => 'Pág. Contato',
    'footer' => 'Rodapé',
];

admin_header('Textos do site', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Site</p>
        <h1 class="font-display">Textos</h1>
        <p>Edite os textos das páginas. Cada campo tem limite de caracteres para não quebrar o layout no celular.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <nav class="admin-tabs" aria-label="Seções de texto">
        <?php foreach ($tabLabels as $id => $label): ?>
          <a class="admin-tabs__link<?= $tab === $id ? ' is-active' : '' ?>" href="texts.php?tab=<?= h($id) ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.25rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="tab" value="<?= h($tab) ?>">
        <p class="text-muted" style="margin-bottom:1rem;"><?= h($groups[$tab] ?? $tab) ?></p>
        <?php foreach ($defs as $key => $def): ?>
          <?php if (($def['group'] ?? '') !== $tab) continue; ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($def['label']) ?> <span class="admin-charlimit" data-for="<?= h($key) ?>">0/<?= (int) $def['max'] ?></span></label>
            <?php if ((int) $def['max'] > 80): ?>
              <textarea id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" rows="<?= (int) $def['max'] > 200 ? 4 : 2 ?>" maxlength="<?= (int) $def['max'] ?>"><?= h($values[$key] ?? '') ?></textarea>
            <?php else: ?>
              <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" maxlength="<?= (int) $def['max'] ?>" value="<?= h($values[$key] ?? '') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar esta seção</button>
      </form>
      <script src="admin-limits.js"></script>
<?php
admin_footer();
