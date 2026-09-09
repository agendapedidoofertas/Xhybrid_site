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
$values = settings_all(db());

$lookBundles = [
    'xhybrid-signature' => ['theme' => 'preto', 'font' => 'saas', 'layout' => 'soft', 'media' => 'classic'],
    'tech-glass' => ['theme' => 'preto', 'font' => 'tech', 'layout' => 'soft', 'media' => 'classic'],
    'editorial' => ['theme' => 'branco', 'font' => 'editorial', 'layout' => 'editorial', 'media' => 'flip'],
    'sharp-saas' => ['theme' => 'graphite', 'font' => 'saas', 'layout' => 'sharp', 'media' => 'media-wide'],
    'warm-studio' => ['theme' => 'marrom-claro', 'font' => 'classic', 'layout' => 'loft', 'media' => 'center'],
    'neon-night' => ['theme' => 'neon', 'font' => 'mono', 'layout' => 'strip', 'media' => 'hero-flip'],
    'obsidian' => ['theme' => 'preto', 'font' => 'display', 'layout' => 'frame', 'media' => 'copy-wide'],
    'navy-depth' => ['theme' => 'midnight', 'font' => 'geometric', 'layout' => 'magazine', 'media' => 'about-flip'],
    'ash-glass' => ['theme' => 'slate', 'font' => 'saas', 'layout' => 'compact', 'media' => 'stack-media'],
    'ivory-soft' => ['theme' => 'cinza', 'font' => 'soft', 'layout' => 'pill', 'media' => 'stack-copy'],
    'petal-sky' => ['theme' => 'peonia', 'font' => 'rounded', 'layout' => 'bento', 'media' => 'flip'],
    'crimson-volt' => ['theme' => 'vinho', 'font' => 'display', 'layout' => 'sharp', 'media' => 'classic'],
    'azure-blast' => ['theme' => 'azul', 'font' => 'geometric', 'layout' => 'frame', 'media' => 'media-wide'],
    'volt-lime' => ['theme' => 'lime', 'font' => 'mono', 'layout' => 'strip', 'media' => 'hero-flip'],
    'amber-flare' => ['theme' => 'amber', 'font' => 'saas', 'layout' => 'bento', 'media' => 'flip'],
    'berry-pop' => ['theme' => 'berry', 'font' => 'rounded', 'layout' => 'pill', 'media' => 'center'],
    'teal-rush' => ['theme' => 'teal', 'font' => 'tech', 'layout' => 'soft', 'media' => 'copy-wide'],
    'indigo-flare' => ['theme' => 'indigo', 'font' => 'display', 'layout' => 'magazine', 'media' => 'about-flip'],
    'fire-sunset' => ['theme' => 'sunset', 'font' => 'condensed', 'layout' => 'loft', 'media' => 'stack-media'],
    'copper-heat' => ['theme' => 'cobre', 'font' => 'classic', 'layout' => 'frame', 'media' => 'classic'],
    'ocean-vivid' => ['theme' => 'oceano', 'font' => 'soft', 'layout' => 'editorial', 'media' => 'flip'],
    'blood-noir' => ['theme' => 'sangue', 'font' => 'display', 'layout' => 'sharp', 'media' => 'classic'],
    'violet-pulse' => ['theme' => 'violeta', 'font' => 'geometric', 'layout' => 'magazine', 'media' => 'about-flip'],
    'neon-orchid' => ['theme' => 'neon-roxo', 'font' => 'mono', 'layout' => 'strip', 'media' => 'hero-flip'],
    'plum-ember' => ['theme' => 'ameixa', 'font' => 'classic', 'layout' => 'frame', 'media' => 'media-wide'],
    'cyber-magenta' => ['theme' => 'fuchsia-night', 'font' => 'saas', 'layout' => 'soft', 'media' => 'copy-wide'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $look = (string) ($_POST['appearance_look'] ?? 'xhybrid-signature');
    if (!isset($lookBundles[$look])) {
        $look = 'xhybrid-signature';
    }
    $bundle = $lookBundles[$look];
    $input = [
        'appearance_look' => $look,
        'appearance_theme' => $bundle['theme'],
        'appearance_font' => $bundle['font'],
        'appearance_layout' => $bundle['layout'],
        'appearance_media' => $bundle['media'],
    ];
    try {
        settings_save_many(db(), $input);
        header('Location: appearance.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        $values['appearance_look'] = $look;
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Aparência publicada no site.';
}

$look = $values['appearance_look'] ?? 'xhybrid-signature';
if (!isset($lookBundles[$look])) {
    $look = 'xhybrid-signature';
}

admin_header('Aparência', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Site</p>
        <h1 class="font-display">Aparência</h1>
        <p>Escolha um look pronto — cada opção muda o site por completo. Use a demonstração ao lado antes de publicar.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <div class="admin-appearance" id="admin-appearance" data-selected-look="<?= h($look) ?>">

        <div class="admin-appearance__toolbar">
          <button type="button" class="btn btn-outline" id="appearance-demo-toggle" aria-pressed="false">
            Demonstração
          </button>
          <p class="admin-appearance__hint text-muted" id="appearance-status">Clique em um look para sentir a mudança na demo.</p>
        </div>

        <div class="admin-appearance__workspace">
          <form method="post" class="admin-appearance__controls contact-form" id="appearance-form">
            <?= csrf_field() ?>
            <input type="hidden" name="appearance_look" id="field-look" value="<?= h($look) ?>">

            <section class="admin-appearance__section">
              <h2 class="admin-appearance__label">Looks prontos</h2>
              <div class="admin-appearance__looks" id="opt-looks" role="listbox" aria-label="Looks do site"></div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar e publicar</button>
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
      <script>
        applySiteSettings(<?= json_encode([
            'site_plan' => $values['site_plan'] ?? 'profissional',
            'feature_looks_premium' => $values['feature_looks_premium'] ?? '0',
            'appearance_look' => $values['appearance_look'] ?? 'xhybrid-signature',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
      </script>
      <script src="appearance.js"></script>
<?php
admin_footer();
