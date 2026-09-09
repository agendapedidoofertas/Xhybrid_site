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

$pageKeys = [
    'feature_page_sobre',
    'feature_page_galeria',
    'feature_page_contato',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $input = [];
    foreach ($defs as $key => $def) {
        if (($def['group'] ?? '') !== 'sections') {
            continue;
        }
        $input[$key] = isset($_POST[$key]) ? '1' : '0';
    }
    foreach ($pageKeys as $key) {
        $input[$key] = isset($_POST[$key]) ? '1' : '0';
    }
    try {
        settings_save_many(db(), $input);
        header('Location: sections.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        $values = settings_all(db());
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Visibilidade atualizada.';
    $values = settings_all(db());
}

$pageLabels = [
    'feature_page_sobre' => 'Sobre — menu, rodapé e página',
    'feature_page_galeria' => 'Projetos — menu, rodapé, botões e página',
    'feature_page_contato' => 'Contato — menu, rodapé e página',
];

admin_header('Seções', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Admin</p>
        <h1 class="font-display">Ocultar / mostrar</h1>
        <p>Desmarque para esconder do site. Exemplo: desligar <strong>Projetos</strong> remove o item do menu, o botão “Ver projetos” e bloqueia a página.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>

        <h2 class="admin-appearance__label">Páginas do menu</h2>
        <p class="text-muted" style="margin:0 0 0.75rem;">Início sempre permanece. Desmarque o que não quiser exibir.</p>
        <?php foreach ($pageKeys as $key): ?>
          <label class="admin-check">
            <input type="checkbox" name="<?= h($key) ?>" value="1" <?= ($values[$key] ?? '1') === '1' ? 'checked' : '' ?>>
            Mostrar <?= h($pageLabels[$key] ?? ($defs[$key]['label'] ?? $key)) ?>
          </label>
        <?php endforeach; ?>

        <h2 class="admin-appearance__label" style="margin-top:1.5rem;">Seções da home</h2>
        <?php foreach ($defs as $key => $def): ?>
          <?php if (($def['group'] ?? '') !== 'sections') continue; ?>
          <label class="admin-check">
            <input type="checkbox" name="<?= h($key) ?>" value="1" <?= ($values[$key] ?? '1') === '1' ? 'checked' : '' ?>>
            Mostrar <?= h($def['label']) ?>
          </label>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="margin-top:1.25rem;">Salvar visibilidade</button>
        <a href="../index.html" class="btn btn-outline" style="margin-top:1.25rem;margin-left:0.5rem;" target="_blank" rel="noopener">Pré-visualizar site</a>
      </form>
<?php
admin_footer();
