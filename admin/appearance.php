<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';
require_once dirname(__DIR__) . '/lib/appearance_catalog.php';
require_once dirname(__DIR__) . '/lib/appearance_combinations.php';

auth_boot_session();
$user = require_page('appearance');
if (!user_is_admin($user) && !user_is_client($user)) {
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
$swatches = appearance_theme_swatches();
$niche = $leadRow ? (string) ($leadRow['site_preset'] ?? 'eletricista') : 'eletricista';

$lookBundles = [
    'xhybrid-signature' => ['theme' => 'preto', 'font' => 'saas', 'layout' => 'soft', 'media' => 'classic'],
    'tech-glass' => ['theme' => 'preto', 'font' => 'tech', 'layout' => 'soft', 'media' => 'classic'],
    'editorial' => ['theme' => 'branco', 'font' => 'editorial', 'layout' => 'editorial', 'media' => 'flip'],
    'sharp-saas' => ['theme' => 'graphite', 'font' => 'saas', 'layout' => 'sharp', 'media' => 'center'],
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
    $comboId = '';
    $look = trim((string) ($_POST['appearance_look'] ?? ''));
    $theme = appearance_theme_alias((string) ($_POST['appearance_theme'] ?? ''));
    $font = appearance_font_alias((string) ($_POST['appearance_font'] ?? ''));
    $skin = trim((string) ($_POST['framework_skin'] ?? 'none'));
    if (!in_array($skin, appearance_framework_skin_ids(), true)) {
        $skin = 'none';
    }
    $bootswatch = trim((string) ($_POST['framework_bootswatch'] ?? ''));

    $layout = (string) ($values['appearance_layout'] ?? 'soft');
    $media = (string) ($values['appearance_media'] ?? 'classic');

    if ($look !== '' && isset($lookBundles[$look])) {
        $bundle = $lookBundles[$look];
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
        'appearance_combination_id' => $comboId,
        'framework_skin' => $skin,
        'framework_bootswatch' => $bootswatch,
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
$skin = (string) ($values['framework_skin'] ?? 'none');
$themeGroups = appearance_themes_by_palette();

admin_header('Aparência', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId . ' · ' . h($niche) : 'Site' ?></p>
        <h1 class="font-display">Aparência</h1>
        <p>Escolha look, cor e fonte com exemplos visuais.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide admin-appearance-form" style="margin-top:1.25rem;" id="appearance-form">
        <?= csrf_field() ?>
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
        <input type="hidden" name="framework_bootswatch" id="framework_bootswatch" value="<?= h((string) ($values['framework_bootswatch'] ?? '')) ?>">
        <input type="hidden" name="appearance_combination_id" value="">

        <div class="admin-appearance" id="admin-appearance" data-selected-look="<?= h($look) ?>">
          <section class="admin-appearance__section">
            <h2 class="admin-appearance__label">Looks prontos</h2>
            <p class="text-muted admin-appearance__hint" id="appearance-status">Toque em um look para aplicar cor + fonte + molde.</p>
            <div class="admin-appearance__looks" id="opt-looks" role="listbox" aria-label="Looks do site"></div>
          </section>
        </div>
        <input type="hidden" id="field-look" value="<?= h($look) ?>">

        <section class="admin-appearance__section">
          <h2 class="admin-appearance__label">Tema / cor</h2>
          <p class="text-muted admin-appearance__hint">Cores agrupadas por paleta — mesmo formato dos looks.</p>
          <div class="admin-appearance__looks" id="theme-picker" role="listbox" aria-label="Temas de cor">
            <?php foreach ($themeGroups as $group): ?>
              <div class="admin-appearance__palette" role="group" aria-label="<?= h($group['name']) ?>">
                <h3 class="admin-appearance__palette-title"><?= h($group['name']) ?></h3>
                <div class="admin-appearance__palette-list">
                  <?php foreach ($group['themes'] as $tmeta): ?>
                    <?php
                      $tid = $tmeta['id'];
                      $chips = $swatches[$tid] ?? ['#111', '#eee', '#888'];
                    ?>
                    <button type="button"
                      class="admin-appearance__look<?= $theme === $tid ? ' is-active' : '' ?>"
                      data-theme-id="<?= h($tid) ?>"
                      role="option"
                      aria-selected="<?= $theme === $tid ? 'true' : 'false' ?>"
                      title="<?= h($tmeta['desc']) ?>">
                      <span class="admin-appearance__look-swatch" aria-hidden="true">
                        <?php foreach ($chips as $hex): ?><span style="background:<?= h($hex) ?>"></span><?php endforeach; ?>
                      </span>
                      <span class="admin-appearance__look-name"><?= h($tmeta['label']) ?></span>
                      <span class="admin-appearance__look-desc"><?= h($tmeta['desc']) ?></span>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <select id="appearance_theme" name="appearance_theme" class="admin-sr-only" tabindex="-1" aria-hidden="true">
            <?php foreach ($themes as $id => $meta): ?>
              <?php $chips = $swatches[$id] ?? ['#111', '#eee', '#888']; ?>
              <option value="<?= h($id) ?>" <?= $theme === $id ? 'selected' : '' ?>
                data-swatch="<?= h(implode(',', $chips)) ?>"><?= h($meta['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </section>

        <div class="form-group">
          <label for="appearance_font">Fonte</label>
          <div class="admin-combo-grid admin-combo-grid--fonts" id="font-picker" role="listbox" aria-label="Temas de fonte">
            <?php foreach ($fonts as $id => $meta): ?>
              <button type="button" class="admin-combo-card admin-font-card<?= $font === $id ? ' is-selected' : '' ?>"
                data-font-id="<?= h($id) ?>" role="option" aria-selected="<?= $font === $id ? 'true' : 'false' ?>"
                style="font-family:<?= h($meta['display']) ?>, sans-serif">
                <span class="admin-font-card__aa">Aa</span>
                <span class="admin-combo-card__id"><?= h($meta['label']) ?></span>
                <span class="admin-combo-font"><?= h($meta['display']) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
          <select id="appearance_font" name="appearance_font" class="admin-sr-only" tabindex="-1" aria-hidden="true">
            <?php foreach ($fonts as $id => $meta): ?>
              <option value="<?= h($id) ?>" <?= $font === $id ? 'selected' : '' ?>
                data-display="<?= h($meta['display']) ?>"><?= h($meta['label']) ?> — <?= h($meta['display']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="framework_skin">Framework skin</label>
          <div class="admin-skin-row" id="skin-picker">
            <?php
            $skinLabels = [
                'none' => ['t' => 'Xhybrid', 'd' => 'CSS próprio'],
                'bootswatch' => ['t' => 'Bootswatch', 'd' => 'Bootstrap tema'],
                'bulma' => ['t' => 'Bulma', 'd' => 'CSS Bulma'],
                'tailwind' => ['t' => 'Tailwind', 'd' => 'Ritmo SaaS'],
            ];
            foreach (appearance_framework_skin_ids() as $sid):
              $lab = $skinLabels[$sid] ?? ['t' => $sid, 'd' => ''];
            ?>
              <button type="button" class="admin-skin-chip<?= $skin === $sid ? ' is-selected' : '' ?>" data-skin-id="<?= h($sid) ?>">
                <strong><?= h($lab['t']) ?></strong>
                <span><?= h($lab['d']) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
          <select id="framework_skin" name="framework_skin" class="admin-sr-only" tabindex="-1" aria-hidden="true">
            <?php foreach (appearance_framework_skin_ids() as $sid): ?>
              <option value="<?= h($sid) ?>" <?= $skin === $sid ? 'selected' : '' ?>><?= h($sid) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <input type="hidden" id="appearance_look" name="appearance_look" value="<?= h($look) ?>">

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar e publicar</button>
      </form>

      <script src="../js/data.js"></script>
      <script>
        applySiteSettings(<?= json_encode([
            'site_plan' => $values['site_plan'] ?? 'medium',
            'feature_looks_premium' => $values['feature_looks_premium'] ?? '0',
            'appearance_look' => $look,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>);
      </script>
      <script src="appearance.js"></script>
      <script>
        (function () {
          var themeSel = document.getElementById('appearance_theme');
          var fontSel = document.getElementById('appearance_font');
          var skinSel = document.getElementById('framework_skin');
          var themePicker = document.getElementById('theme-picker');
          var fontPicker = document.getElementById('font-picker');
          var skinPicker = document.getElementById('skin-picker');

          function refreshTheme() {
            if (!themeSel || !themePicker) return;
            themePicker.querySelectorAll('[data-theme-id]').forEach(function (btn) {
              var on = btn.getAttribute('data-theme-id') === themeSel.value;
              btn.classList.toggle('is-active', on);
              btn.setAttribute('aria-selected', on ? 'true' : 'false');
            });
          }
          function refreshFont() {
            if (!fontSel) return;
            if (fontPicker) {
              fontPicker.querySelectorAll('[data-font-id]').forEach(function (btn) {
                var on = btn.getAttribute('data-font-id') === fontSel.value;
                btn.classList.toggle('is-selected', on);
                btn.setAttribute('aria-selected', on ? 'true' : 'false');
              });
            }
          }
          function refreshSkin() {
            if (!skinSel || !skinPicker) return;
            skinPicker.querySelectorAll('[data-skin-id]').forEach(function (btn) {
              btn.classList.toggle('is-selected', btn.getAttribute('data-skin-id') === skinSel.value);
            });
          }

          if (themeSel) themeSel.addEventListener('change', refreshTheme);
          if (fontSel) fontSel.addEventListener('change', refreshFont);
          if (skinSel) skinSel.addEventListener('change', refreshSkin);

          if (themePicker) {
            themePicker.addEventListener('click', function (e) {
              var btn = e.target.closest('[data-theme-id]');
              if (!btn || !themeSel) return;
              themeSel.value = btn.getAttribute('data-theme-id');
              themeSel.dispatchEvent(new Event('change'));
            });
          }
          if (fontPicker) {
            fontPicker.addEventListener('click', function (e) {
              var btn = e.target.closest('[data-font-id]');
              if (!btn || !fontSel) return;
              fontSel.value = btn.getAttribute('data-font-id');
              fontSel.dispatchEvent(new Event('change'));
            });
          }
          if (skinPicker) {
            skinPicker.addEventListener('click', function (e) {
              var btn = e.target.closest('[data-skin-id]');
              if (!btn || !skinSel) return;
              skinSel.value = btn.getAttribute('data-skin-id');
              skinSel.dispatchEvent(new Event('change'));
            });
          }

          window.__xhybridAppearanceSyncFromLook = function (preset) {
            if (!preset) return;
            if (themeSel && preset.theme) {
              themeSel.value = preset.theme;
              themeSel.dispatchEvent(new Event('change'));
            }
            if (fontSel && preset.font) {
              fontSel.value = preset.font;
              fontSel.dispatchEvent(new Event('change'));
            }
          };

          refreshTheme();
          refreshFont();
          refreshSkin();
        })();
      </script>
<?php
admin_footer();
