<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/plans.php';

auth_boot_session();
$user = require_role_admin();

$flash = '';
$error = '';
$defs = settings_definitions();
$values = settings_all(db());
$labels = plan_labels();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? 'save');
    try {
        if ($action === 'apply_bundle') {
            $plan = (string) ($_POST['site_plan'] ?? 'essencial');
            plan_apply(db(), $plan);
            header('Location: plan.php?ok=bundle');
            exit;
        }

        $input = [];
        foreach ($defs as $key => $def) {
            $group = $def['group'] ?? '';
            if ($group !== 'plan' && $group !== 'sections') {
                continue;
            }
            if ($key === 'site_plan') {
                $input[$key] = (string) ($_POST[$key] ?? 'essencial');
                continue;
            }
            if ($key === 'limit_services' || $key === 'limit_gallery') {
                $input[$key] = (string) ($_POST[$key] ?? '');
                continue;
            }
            if (($def['type'] ?? '') === 'choice') {
                $input[$key] = isset($_POST[$key]) ? '1' : '0';
            }
        }
        // Urgência acompanha o plano comercial
        if (isset($_POST['urgency_enabled'])) {
            $input['urgency_enabled'] = '1';
        } else {
            $input['urgency_enabled'] = '0';
        }

        settings_save_many(db(), $input);
        header('Location: plan.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        $values = settings_all(db());
    }
}

if (isset($_GET['ok'])) {
    $flash = $_GET['ok'] === 'bundle'
        ? 'Pacote do plano aplicado (páginas, seções, limites e look).'
        : 'Plano e flags salvos.';
    $values = settings_all(db());
}

$flagKeys = [
    'feature_page_sobre',
    'feature_page_galeria',
    'feature_page_contato',
    'feature_animations',
    'feature_looks_premium',
    'feature_preset_nicho',
];
$sectionKeys = [
    'section_hero',
    'section_features',
    'section_works',
    'section_area',
    'section_testimonials',
    'section_faq',
    'section_cta',
];

admin_header('Plano', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Admin</p>
        <h1 class="font-display">Plano do cliente</h1>
        <p>Escolha o plano pago e ligue/desligue o que o site pode ter. O editor não vê esta tela.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="apply_bundle">
        <div class="form-group">
          <label for="bundle_plan">Aplicar pacote pronto</label>
          <select id="bundle_plan" name="site_plan" class="form-input">
            <?php foreach ($labels as $id => $label): ?>
              <option value="<?= h($id) ?>" <?= ($values['site_plan'] ?? '') === $id ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" onclick="return confirm('Isso redefine páginas, seções, limites e look do plano. Continuar?');">
          Aplicar pacote do plano
        </button>
      </form>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">

        <div class="form-group">
          <label for="site_plan">Plano contratado</label>
          <select id="site_plan" name="site_plan" class="form-input">
            <?php foreach ($labels as $id => $label): ?>
              <option value="<?= h($id) ?>" <?= ($values['site_plan'] ?? '') === $id ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Páginas do menu (ocultar / mostrar)</h2>
        <p class="text-muted" style="margin:0 0 0.75rem;">Mesmas opções de <a href="sections.php">Seções</a>. Ex.: desmarque Projetos para tirar do menu.</p>
        <?php foreach ($flagKeys as $key): ?>
          <?php if (str_starts_with($key, 'feature_page_')): ?>
          <?php $def = $defs[$key]; ?>
          <label class="admin-check">
            <input type="checkbox" name="<?= h($key) ?>" value="1" <?= ($values[$key] ?? '0') === '1' ? 'checked' : '' ?>>
            <?= h($def['label']) ?>
          </label>
          <?php endif; ?>
        <?php endforeach; ?>

        <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Recursos do plano</h2>
        <?php foreach ($flagKeys as $key): ?>
          <?php if (str_starts_with($key, 'feature_page_')) continue; ?>
          <?php $def = $defs[$key]; ?>
          <label class="admin-check">
            <input type="checkbox" name="<?= h($key) ?>" value="1" <?= ($values[$key] ?? '0') === '1' ? 'checked' : '' ?>>
            <?= h($def['label']) ?>
          </label>
        <?php endforeach; ?>
        <label class="admin-check">
          <input type="checkbox" name="urgency_enabled" value="1" <?= ($values['urgency_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
          Badge de urgência (24h)
        </label>

        <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Seções da home</h2>
        <?php foreach ($sectionKeys as $key): ?>
          <?php $def = $defs[$key]; ?>
          <label class="admin-check">
            <input type="checkbox" name="<?= h($key) ?>" value="1" <?= ($values[$key] ?? '0') === '1' ? 'checked' : '' ?>>
            <?= h($def['label']) ?>
          </label>
        <?php endforeach; ?>

        <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Limites</h2>
        <div class="form-group">
          <label for="limit_services">Máx. serviços ativos</label>
          <input id="limit_services" name="limit_services" class="form-input" maxlength="3" value="<?= h($values['limit_services'] ?? '6') ?>">
        </div>
        <div class="form-group">
          <label for="limit_gallery">Máx. imagens na galeria (além de logo/favicon/hero/casal)</label>
          <input id="limit_gallery" name="limit_gallery" class="form-input" maxlength="3" value="<?= h($values['limit_gallery'] ?? '12') ?>">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar plano e flags</button>
      </form>
<?php
admin_footer();
