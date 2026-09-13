<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';

auth_boot_session();
$user = require_page('texts');

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
}

$allowedTabs = ['menu', 'home', 'sobre', 'galeria', 'contato_page', 'footer', 'testimonials', 'faq'];
$tab = (string) ($_GET['tab'] ?? 'menu');
if (!in_array($tab, $allowedTabs, true)) {
    $tab = 'menu';
}

$flash = '';
$error = '';
$defs = settings_definitions();
$groups = settings_groups();
$values = $leadRow ? lead_admin_settings($leadRow) : settings_all(db());

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
        if ($leadId) {
            lead_admin_save_settings(db(), $leadId, $input);
            header('Location: texts.php?' . lead_admin_qs($leadId) . '&tab=' . urlencode($tab) . '&ok=1');
        } else {
            settings_save_many(db(), $input);
            settings_sync_page_from_text_tab(db(), $tab);
            header('Location: texts.php?tab=' . urlencode($tab) . '&ok=1');
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
    $flash = 'Textos salvos.';
    if ($leadId) {
        $values = lead_admin_settings(lead_admin_resolve($leadId) ?? $leadRow);
    }
}

$tabLabels = [
    'menu' => 'Menu',
    'home' => 'Home',
    'sobre' => 'Sobre',
    'galeria' => 'Galeria',
    'contato_page' => 'Pág. Contato',
    'footer' => 'Rodapé',
    'testimonials' => 'Depoimentos',
    'faq' => 'FAQ',
];

$needsIconLib = $tab === 'home';
$tabQs = ($leadId ? lead_admin_qs($leadId) . '&' : '') . 'tab=';

admin_header('Textos do site', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Site' ?></p>
        <h1 class="font-display">Textos</h1>
        <p>Edite os textos das páginas<?= $leadId ? ' deste lead' : '' ?>.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <nav class="admin-tabs" aria-label="Seções de texto">
        <?php foreach ($tabLabels as $id => $label): ?>
          <a class="admin-tabs__link<?= $tab === $id ? ' is-active' : '' ?>" href="texts.php?<?= h($tabQs . $id) ?>"><?= h($label) ?></a>
        <?php endforeach; ?>
      </nav>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.25rem;">
        <?= csrf_field() ?>
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
        <input type="hidden" name="tab" value="<?= h($tab) ?>">
        <p class="text-muted" style="margin-bottom:1rem;"><?= h($groups[$tab] ?? $tab) ?></p>
        <?php foreach ($defs as $key => $def): ?>
          <?php if (($def['group'] ?? '') !== $tab) continue; ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($def['label']) ?> <span class="admin-charlimit" data-for="<?= h($key) ?>">0/<?= (int) $def['max'] ?></span></label>
            <?php if (($def['type'] ?? '') === 'icon'): ?>
              <?php
                $choices = $def['choices'] ?? feat_icon_catalog();
                $current = (string) ($values[$key] ?? $def['default'] ?? 'layout');
                if (!isset($choices[$current])) {
                    $current = (string) ($def['default'] ?? 'layout');
                }
              ?>
              <input type="hidden" id="<?= h($key) ?>" name="<?= h($key) ?>" value="<?= h($current) ?>">
              <div class="icon-lib" data-icon-library data-for="<?= h($key) ?>" data-value="<?= h($current) ?>">
                <div class="icon-lib__current">
                  <span class="icon-lib__preview" data-icon-preview aria-hidden="true"></span>
                  <div class="icon-lib__preview-meta">
                    <span class="icon-lib__preview-kicker">Selecionado</span>
                    <span class="icon-lib__preview-label" data-icon-preview-label><?= h((string) ($choices[$current] ?? $current)) ?></span>
                  </div>
                </div>
                <input type="search" class="form-input icon-lib__search" data-icon-search placeholder="Buscar ícone…" autocomplete="off">
                <div class="icon-lib__chips" data-icon-chips role="group" aria-label="Categorias"></div>
                <div class="icon-lib__grid" data-icon-grid role="listbox" aria-label="Biblioteca de ícones"></div>
              </div>
            <?php elseif (($def['type'] ?? '') === 'choice'): ?>
              <?php
                $choices = $def['choices'] ?? [];
                $current = (string) ($values[$key] ?? $def['default'] ?? '');
                $isAssoc = is_array($choices) && !array_is_list($choices);
              ?>
              <select id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input">
                <?php foreach ($choices as $optKey => $optLabel): ?>
                  <?php
                    $optValue = $isAssoc ? (string) $optKey : (string) $optLabel;
                    $optText = $isAssoc ? (string) $optLabel : (string) $optLabel;
                  ?>
                  <option value="<?= h($optValue) ?>"<?= $current === $optValue ? ' selected' : '' ?>><?= h($optText) ?></option>
                <?php endforeach; ?>
              </select>
            <?php elseif ((int) $def['max'] > 80): ?>
              <textarea id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" rows="<?= (int) $def['max'] > 200 ? 4 : 2 ?>" maxlength="<?= (int) $def['max'] ?>"><?= h($values[$key] ?? '') ?></textarea>
            <?php else: ?>
              <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" maxlength="<?= (int) $def['max'] ?>" value="<?= h($values[$key] ?? '') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar esta seção</button>
      </form>
      <script src="admin-limits.js"></script>
      <?php if ($needsIconLib): ?>
      <script>
        window.FEAT_ICON_META = <?= json_encode([
            'labels' => feat_icon_catalog(),
            'categories' => feat_icon_categories(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      </script>
      <script src="../js/feat-icons.js"></script>
      <script src="icon-library.js"></script>
      <?php endif; ?>
<?php
admin_footer();
