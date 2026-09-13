<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';
require_once dirname(__DIR__) . '/lib/appearance_catalog.php';

auth_boot_session();
$user = require_page('appearance');
if (!user_is_admin($user) && !user_is_client($user)) {
    // editors don't get appearance unless client_pro already allowed via require_page
    if (!user_can_page($user, 'appearance')) {
        header('Location: index.php');
        exit;
    }
}

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
    lead_admin_set_context($leadId);
} elseif (!user_is_admin($user)) {
    header('Location: index.php');
    exit;
}

$flash = '';
$error = '';
$values = $leadRow ? lead_admin_settings($leadRow) : settings_all(db());
$themes = appearance_themes();
$fonts = appearance_fonts();

$lookBundles = [
    'xhybrid-signature' => ['theme' => 'preto', 'font' => 'saas', 'layout' => 'soft', 'media' => 'classic'],
    'tech-glass' => ['theme' => 'preto', 'font' => 'tech', 'layout' => 'soft', 'media' => 'classic'],
    'editorial' => ['theme' => 'branco', 'font' => 'editorial', 'layout' => 'editorial', 'media' => 'flip'],
    'sharp-saas' => ['theme' => 'graphite', 'font' => 'saas', 'layout' => 'sharp', 'media' => 'media-wide'],
    'warm-studio' => ['theme' => 'marrom-claro', 'font' => 'classic', 'layout' => 'loft', 'media' => 'center'],
    'obsidian' => ['theme' => 'preto', 'font' => 'display', 'layout' => 'frame', 'media' => 'copy-wide'],
    'navy-depth' => ['theme' => 'midnight', 'font' => 'geometric', 'layout' => 'magazine', 'media' => 'about-flip'],
    'ash-glass' => ['theme' => 'slate', 'font' => 'saas', 'layout' => 'compact', 'media' => 'stack-media'],
    'ivory-soft' => ['theme' => 'cinza', 'font' => 'soft', 'layout' => 'pill', 'media' => 'stack-copy'],
    'crimson-volt' => ['theme' => 'vinho', 'font' => 'display', 'layout' => 'sharp', 'media' => 'classic'],
    'azure-blast' => ['theme' => 'azul', 'font' => 'geometric', 'layout' => 'frame', 'media' => 'media-wide'],
    'amber-flare' => ['theme' => 'amber', 'font' => 'saas', 'layout' => 'bento', 'media' => 'flip'],
    'teal-rush' => ['theme' => 'teal', 'font' => 'tech', 'layout' => 'soft', 'media' => 'copy-wide'],
    'indigo-flare' => ['theme' => 'indigo', 'font' => 'display', 'layout' => 'magazine', 'media' => 'about-flip'],
    'fire-sunset' => ['theme' => 'sunset', 'font' => 'condensed', 'layout' => 'loft', 'media' => 'stack-media'],
    'copper-heat' => ['theme' => 'cobre', 'font' => 'classic', 'layout' => 'frame', 'media' => 'classic'],
    'ocean-vivid' => ['theme' => 'oceano', 'font' => 'soft', 'layout' => 'editorial', 'media' => 'flip'],
    'blood-noir' => ['theme' => 'sangue', 'font' => 'display', 'layout' => 'sharp', 'media' => 'classic'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $look = trim((string) ($_POST['appearance_look'] ?? ''));
    $theme = appearance_theme_alias((string) ($_POST['appearance_theme'] ?? ''));
    $font = appearance_font_alias((string) ($_POST['appearance_font'] ?? ''));

    $layout = (string) ($values['appearance_layout'] ?? 'soft');
    $media = (string) ($values['appearance_media'] ?? 'classic');
    if ($look !== '' && isset($lookBundles[$look])) {
        $bundle = $lookBundles[$look];
        // Look opcional: se tema/fonte não vieram do POST custom, usa bundle
        if (trim((string) ($_POST['appearance_theme'] ?? '')) === '') {
            $theme = appearance_theme_alias($bundle['theme']);
        }
        if (trim((string) ($_POST['appearance_font'] ?? '')) === '') {
            $font = appearance_font_alias($bundle['font']);
        }
        $layout = $bundle['layout'];
        $media = $bundle['media'];
    } elseif ($look === '' || !isset($lookBundles[$look])) {
        $look = (string) ($values['appearance_look'] ?? 'xhybrid-signature');
        if (!isset($lookBundles[$look])) {
            $look = 'xhybrid-signature';
        }
    }

    $input = [
        'appearance_look' => $look,
        'appearance_theme' => $theme,
        'appearance_font' => $font,
        'appearance_layout' => $layout,
        'appearance_media' => $media,
    ];
    try {
        if ($leadId) {
            lead_admin_save_settings(db(), $leadId, $input);
            header('Location: appearance.php?' . lead_admin_qs($leadId) . '&ok=1');
        } else {
            settings_save_many(db(), $input);
            header('Location: appearance.php?ok=1');
        }
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        $values = array_merge($values, $input);
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Aparência publicada.';
    $values = $leadId
        ? lead_admin_settings(lead_admin_resolve($leadId) ?? $leadRow)
        : settings_all(db());
}

$look = (string) ($values['appearance_look'] ?? 'xhybrid-signature');
$theme = appearance_theme_alias((string) ($values['appearance_theme'] ?? 'preto'));
$font = appearance_font_alias((string) ($values['appearance_font'] ?? 'tech'));

admin_header('Aparência', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Site' ?></p>
        <h1 class="font-display">Aparência</h1>
        <p>Escolha tema e fonte independentemente. Look opcional aplica um pacote completo (layout/mídia).</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;" id="appearance-form">
        <?= csrf_field() ?>
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>

        <div class="form-group">
          <label for="appearance_theme">Tema / cor</label>
          <select id="appearance_theme" name="appearance_theme" class="form-input">
            <?php foreach ($themes as $id => $meta): ?>
              <option value="<?= h($id) ?>" <?= $theme === $id ? 'selected' : '' ?>><?= h($meta['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="appearance_font">Fonte</label>
          <select id="appearance_font" name="appearance_font" class="form-input">
            <?php foreach ($fonts as $id => $meta): ?>
              <option value="<?= h($id) ?>" <?= $font === $id ? 'selected' : '' ?>><?= h($meta['label']) ?> — <?= h($meta['display']) ?> / <?= h($meta['body']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="appearance_look">Look (opcional)</label>
          <select id="appearance_look" name="appearance_look" class="form-input">
            <?php foreach ($lookBundles as $id => $_b): ?>
              <option value="<?= h($id) ?>" <?= $look === $id ? 'selected' : '' ?>><?= h($id) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="text-muted" style="margin:0.35rem 0 0;font-size:0.85rem;">Se escolher um look, layout/mídia do pacote são aplicados; tema e fonte do formulário prevalecem se preenchidos.</p>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar e publicar</button>
      </form>

      <div class="admin-appearance" id="admin-appearance" data-selected-look="<?= h($look) ?>" style="margin-top:2rem;">
        <section class="admin-appearance__section">
          <h2 class="admin-appearance__label">Looks prontos (atalho)</h2>
          <p class="text-muted" style="margin:0 0 0.75rem;font-size:0.85rem;" id="appearance-status">Clique em um look para preencher o seletor; salve para publicar.</p>
          <div class="admin-appearance__looks" id="opt-looks" role="listbox" aria-label="Looks do site"></div>
        </section>
      </div>
      <input type="hidden" id="field-look" value="<?= h($look) ?>">
      <script src="../js/data.js"></script>
      <script>
        applySiteSettings(<?= json_encode([
            'site_plan' => $values['site_plan'] ?? 'medium',
            'feature_looks_premium' => $values['feature_looks_premium'] ?? '0',
            'appearance_look' => $look,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
      </script>
      <script src="appearance.js"></script>
<?php
admin_footer();
