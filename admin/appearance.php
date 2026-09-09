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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $input = [
        'appearance_theme' => (string) ($_POST['appearance_theme'] ?? ''),
        'appearance_font' => (string) ($_POST['appearance_font'] ?? ''),
        'appearance_layout' => (string) ($_POST['appearance_layout'] ?? ''),
        'appearance_media' => (string) ($_POST['appearance_media'] ?? ''),
    ];
    try {
        settings_save_many(db(), $input);
        header('Location: appearance.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        foreach ($input as $k => $v) {
            $values[$k] = settings_sanitize($k, $v);
        }
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Aparência publicada no site.';
}

$theme = $values['appearance_theme'] ?? 'preto';
$font = $values['appearance_font'] ?? 'tech';
$layout = $values['appearance_layout'] ?? 'soft';
$media = $values['appearance_media'] ?? 'classic';

admin_header('Aparência', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Site</p>
        <h1 class="font-display">Aparência</h1>
        <p>Só administradores alteram cores, fontes, molde e posição das imagens. Use a demonstração para ver o site ao vivo antes de salvar.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <div class="admin-appearance" id="admin-appearance"
        data-theme="<?= h($theme) ?>"
        data-font="<?= h($font) ?>"
        data-layout="<?= h($layout) ?>"
        data-media="<?= h($media) ?>">

        <div class="admin-appearance__toolbar">
          <button type="button" class="btn btn-outline" id="appearance-demo-toggle" aria-pressed="false">
            Demonstração
          </button>
          <button type="button" class="btn btn-outline" id="appearance-remix">Remix</button>
          <p class="admin-appearance__hint text-muted" id="appearance-status">Alterações na demo não publicam até salvar.</p>
        </div>

        <div class="admin-appearance__workspace">
          <form method="post" class="admin-appearance__controls contact-form" id="appearance-form">
            <?= csrf_field() ?>
            <input type="hidden" name="appearance_theme" id="field-theme" value="<?= h($theme) ?>">
            <input type="hidden" name="appearance_font" id="field-font" value="<?= h($font) ?>">
            <input type="hidden" name="appearance_layout" id="field-layout" value="<?= h($layout) ?>">
            <input type="hidden" name="appearance_media" id="field-media" value="<?= h($media) ?>">

            <section class="admin-appearance__section">
              <h2 class="admin-appearance__label">Cores</h2>
              <div class="admin-appearance__swatches" id="opt-themes" role="listbox" aria-label="Cores"></div>
            </section>

            <section class="admin-appearance__section">
              <h2 class="admin-appearance__label">Fonte</h2>
              <div class="admin-appearance__list" id="opt-fonts" role="listbox" aria-label="Fonte"></div>
            </section>

            <section class="admin-appearance__section">
              <h2 class="admin-appearance__label">Molde</h2>
              <div class="admin-appearance__list" id="opt-layouts" role="listbox" aria-label="Molde"></div>
            </section>

            <section class="admin-appearance__section">
              <h2 class="admin-appearance__label">Imagens / layout</h2>
              <div class="admin-appearance__list" id="opt-media" role="listbox" aria-label="Imagens"></div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">Salvar e publicar</button>
          </form>

          <aside class="admin-appearance__demo" id="appearance-demo" hidden>
            <div class="admin-appearance__demo-bar">
              <p class="admin-appearance__demo-title">Pré-visualização do site</p>
              <div class="admin-appearance__demo-pages" role="tablist" aria-label="Página da demo">
                <button type="button" class="admin-appearance__page-btn is-active" data-preview-page="../index.html">Home</button>
                <button type="button" class="admin-appearance__page-btn" data-preview-page="../sobre.html">Sobre</button>
                <button type="button" class="admin-appearance__page-btn" data-preview-page="../galeria.html">Projetos</button>
                <button type="button" class="admin-appearance__page-btn" data-preview-page="../contato.html">Contato</button>
              </div>
            </div>
            <iframe
              id="appearance-frame"
              class="admin-appearance__frame"
              title="Demonstração do site"
              src="../index.html?preview=1"
            ></iframe>
          </aside>
        </div>
      </div>

      <script src="../js/data.js"></script>
      <script src="appearance.js"></script>
<?php
admin_footer();
