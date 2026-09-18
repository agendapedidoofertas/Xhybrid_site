<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/plans.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';
require_once dirname(__DIR__) . '/lib/permissions.php';
require_once dirname(__DIR__) . '/lib/public_offer.php';

auth_boot_session();
$user = require_page('plan');

if (!user_is_staff($user)) {
    http_response_code(403);
    admin_header('Sem permissão', $user);
    echo '<p class="admin-flash admin-flash--error">Apenas a equipe da agência pode alterar o plano.</p>';
    admin_footer();
    exit;
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
} else {
    $user = require_role_admin();
}

$flash = '';
$error = '';
$defs = settings_definitions();
$values = $leadRow ? lead_admin_settings($leadRow) : settings_all(db());
$labels = plan_labels();
$values['site_plan'] = plan_normalize((string) ($values['site_plan'] ?? 'medium'));
$isAgencyAdmin = $leadId === null && user_is_admin($user);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? 'save');
    try {
        if ($action === 'save_offer') {
            if (!$isAgencyAdmin) {
                throw new RuntimeException('Só o Admin da agência edita a oferta pública.');
            }
            $input = [
                'offer_intro' => (string) ($_POST['offer_intro'] ?? ''),
                'offer_eyebrow' => (string) ($_POST['offer_eyebrow'] ?? ''),
                'offer_title' => (string) ($_POST['offer_title'] ?? ''),
                'offer_highlight' => plan_normalize((string) ($_POST['offer_highlight'] ?? 'pleno')),
                'offer_referral_note' => (string) ($_POST['offer_referral_note'] ?? ''),
                'offer_terms_summary' => (string) ($_POST['offer_terms_summary'] ?? ''),
                'offer_terms_url' => (string) ($_POST['offer_terms_url'] ?? '/termos.html'),
                'offer_privacy_summary' => (string) ($_POST['offer_privacy_summary'] ?? ''),
                'offer_privacy_url' => (string) ($_POST['offer_privacy_url'] ?? '/privacidade.html'),
            ];
            foreach (plan_ids() as $pid) {
                $input["offer_{$pid}_label"] = (string) ($_POST["offer_{$pid}_label"] ?? '');
                $centsPlan = public_offer_brl_to_cents_string((string) ($_POST["offer_{$pid}_plan_brl"] ?? ''));
                $centsMaint = public_offer_brl_to_cents_string((string) ($_POST["offer_{$pid}_maint_brl"] ?? ''));
                $input["offer_{$pid}_plan_cents"] = $centsPlan !== '' ? $centsPlan : (string) ($_POST["offer_{$pid}_plan_cents"] ?? '');
                $input["offer_{$pid}_maint_cents"] = $centsMaint !== '' ? $centsMaint : (string) ($_POST["offer_{$pid}_maint_cents"] ?? '');
                $input["offer_{$pid}_bullets"] = (string) ($_POST["offer_{$pid}_bullets"] ?? '');
            }
            public_offer_save(db(), $input);
            header('Location: plan.php?ok=offer');
            exit;
        }

        if ($action === 'save_permissions') {
            if (!$isAgencyAdmin) {
                throw new RuntimeException('Só o Admin da agência edita a matriz de permissões.');
            }
            foreach (permissions_editable_roles() as $role) {
                $pages = $_POST['perm_role'][$role] ?? [];
                if (!is_array($pages)) {
                    $pages = [];
                }
                permissions_save_role_pages(db(), $role, array_map('strval', $pages));
            }
            foreach (['basic', 'medium', 'pro'] as $plan) {
                $feats = [];
                foreach (array_keys(permissions_plan_feature_catalog()) as $feat) {
                    $feats[$feat] = isset($_POST['perm_plan'][$plan][$feat]);
                }
                permissions_save_plan_features(db(), $plan, $feats);
            }
            header('Location: plan.php?ok=perms');
            exit;
        }

        if ($action === 'reset_permissions') {
            if (!$isAgencyAdmin) {
                throw new RuntimeException('Só o Admin da agência pode resetar permissões.');
            }
            permissions_reset_defaults(db());
            header('Location: plan.php?ok=perms_reset');
            exit;
        }

        if ($action === 'apply_bundle') {
            $plan = plan_normalize((string) ($_POST['site_plan'] ?? 'basic'));
            $bundle = plan_bundle($plan);
            if ($leadId) {
                lead_admin_save_settings(db(), $leadId, $bundle);
                header('Location: plan.php?' . lead_admin_qs($leadId) . '&ok=bundle');
            } else {
                plan_apply(db(), $plan);
                header('Location: plan.php?ok=bundle');
            }
            exit;
        }

        $input = [];
        foreach ($defs as $key => $def) {
            $group = $def['group'] ?? '';
            if ($group !== 'plan' && $group !== 'sections') {
                continue;
            }
            if ($key === 'site_plan') {
                $input[$key] = plan_normalize((string) ($_POST[$key] ?? 'basic'));
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
        $input['urgency_enabled'] = isset($_POST['urgency_enabled']) ? '1' : '0';

        if ($leadId) {
            lead_admin_save_settings(db(), $leadId, $input);
            header('Location: plan.php?' . lead_admin_qs($leadId) . '&ok=1');
        } else {
            settings_save_many(db(), $input);
            header('Location: plan.php?ok=1');
        }
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        $values = $leadId
            ? lead_admin_settings(lead_admin_resolve($leadId) ?? $leadRow)
            : settings_all(db());
    }
}

if (isset($_GET['ok'])) {
    $flash = match ((string) $_GET['ok']) {
        'bundle' => 'Pacote do plano aplicado (páginas, seções, limites e look).',
        'perms' => 'Matriz de permissões salva.',
        'perms_reset' => 'Permissões restauradas para o padrão.',
        'offer' => 'Oferta pública (planos e termos) salva.',
        default => 'Plano e flags salvos.',
    };
    $values = $leadId
        ? lead_admin_settings(lead_admin_resolve($leadId) ?? $leadRow)
        : settings_all(db());
    $values['site_plan'] = plan_normalize((string) ($values['site_plan'] ?? 'medium'));
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

$pageCatalog = permissions_page_catalog();
$featCatalog = permissions_plan_feature_catalog();
$rolePages = [];
foreach (permissions_editable_roles() as $role) {
    $rolePages[$role] = permissions_role_pages(db(), $role);
}
$planFeats = [];
foreach (['basic', 'medium', 'pro'] as $pid) {
    $planFeats[$pid] = permissions_plan_features(db(), $pid);
}
$roleLabels = [
    'editor' => 'Editor',
    'client_medium' => 'Cliente Medium',
    'client_pro' => 'Cliente Pro',
];

$offer = $isAgencyAdmin ? public_offer_get(db()) : null;
$fmtOfferBrl = static function (int $cents): string {
    return number_format($cents / 100, 2, ',', '.');
};

admin_header('Plano', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Admin' ?></p>
        <h1 class="font-display">Plano do cliente</h1>
        <p>Basic / Pleno / Plus — flags e limites<?= $leadId ? ' deste lead' : '' ?>.</p>
        <ul class="text-muted" style="margin:.75rem 0 0;padding-left:1.1rem;font-size:.88rem;line-height:1.45;">
          <?php foreach (plan_blurbs() as $pid => $blurb): ?>
            <li><strong><?= h($labels[$pid] ?? $pid) ?>:</strong> <?= h($blurb) ?></li>
          <?php endforeach; ?>
        </ul>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <?php if ($isAgencyAdmin && is_array($offer)): ?>
      <section class="admin-perm" style="margin-top:1.75rem;">
        <h2 class="font-display" style="font-size:1.35rem;margin:0 0 .35rem;">Oferta pública (Planos + Termos)</h2>
        <p class="text-muted" style="margin:0 0 1rem;font-size:.875rem;">
          Edita a página <a href="/planos.html" target="_blank" rel="noopener">/planos.html</a>,
          preços/bullets e o resumo dos Termos no modal. Cobrança no CRM continua separada.
        </p>
        <form method="post" class="contact-form admin-form admin-form--wide">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save_offer">

          <div class="form-group">
            <label for="offer_eyebrow">Eyebrow (acima do título)</label>
            <input id="offer_eyebrow" name="offer_eyebrow" class="form-input" maxlength="40" value="<?= h((string) ($offer['eyebrow'] ?? 'Oferta')) ?>">
          </div>
          <div class="form-group">
            <label for="offer_title">Título</label>
            <input id="offer_title" name="offer_title" class="form-input" maxlength="60" value="<?= h((string) ($offer['title'] ?? 'Planos')) ?>">
          </div>
          <div class="form-group">
            <label for="offer_intro">Introdução</label>
            <textarea id="offer_intro" name="offer_intro" class="form-input" rows="2" maxlength="320"><?= h((string) $offer['intro']) ?></textarea>
          </div>
          <div class="form-group">
            <label for="offer_highlight">Plano destacado (“Mais escolhido”)</label>
            <select id="offer_highlight" name="offer_highlight" class="form-input">
              <?php foreach (plan_ids() as $pid): ?>
                <option value="<?= h($pid) ?>" <?= ($offer['highlight'] ?? '') === $pid ? 'selected' : '' ?>><?= h($labels[$pid] ?? $pid) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label for="offer_referral_note">Nota de indicação / rodapé</label>
            <textarea id="offer_referral_note" name="offer_referral_note" class="form-input" rows="2" maxlength="280"><?= h((string) $offer['referral_note']) ?></textarea>
          </div>

          <?php foreach (plan_ids() as $pid):
              $op = $offer['plans'][$pid] ?? null;
              if (!is_array($op)) {
                  continue;
              }
          ?>
          <h2 class="admin-appearance__label" style="margin-top:1.25rem;"><?= h((string) $op['label']) ?></h2>
          <div class="form-group">
            <label for="offer_<?= h($pid) ?>_label">Rótulo</label>
            <input id="offer_<?= h($pid) ?>_label" name="offer_<?= h($pid) ?>_label" class="form-input" maxlength="24" value="<?= h((string) $op['label']) ?>">
          </div>
          <div class="form-group">
            <label for="offer_<?= h($pid) ?>_plan_brl">Preço do plano (R$ / mês)</label>
            <input id="offer_<?= h($pid) ?>_plan_brl" name="offer_<?= h($pid) ?>_plan_brl" class="form-input" inputmode="decimal" value="<?= h($fmtOfferBrl((int) $op['planCents'])) ?>">
          </div>
          <div class="form-group">
            <label for="offer_<?= h($pid) ?>_maint_brl">Manutenção (R$ / mês)</label>
            <input id="offer_<?= h($pid) ?>_maint_brl" name="offer_<?= h($pid) ?>_maint_brl" class="form-input" inputmode="decimal" value="<?= h($fmtOfferBrl((int) $op['maintenanceCents'])) ?>">
          </div>
          <div class="form-group">
            <label for="offer_<?= h($pid) ?>_bullets">Bullets (1 por linha)</label>
            <textarea id="offer_<?= h($pid) ?>_bullets" name="offer_<?= h($pid) ?>_bullets" class="form-input" rows="4" maxlength="1200"><?= h(implode("\n", $op['bullets'] ?? [])) ?></textarea>
          </div>
          <?php endforeach; ?>

          <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Termos de Serviço</h2>
          <div class="form-group">
            <label for="offer_terms_summary">Resumo no modal (parágrafos separados por linha em branco)</label>
            <textarea id="offer_terms_summary" name="offer_terms_summary" class="form-input" rows="8" maxlength="6000"><?= h((string) $offer['terms_summary']) ?></textarea>
          </div>
          <div class="form-group">
            <label for="offer_terms_url">URL da página completa</label>
            <input id="offer_terms_url" name="offer_terms_url" class="form-input" maxlength="160" value="<?= h((string) $offer['terms_url']) ?>">
          </div>

          <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Privacidade (LGPD)</h2>
          <div class="form-group">
            <label for="offer_privacy_summary">Resumo no modal (parágrafos separados por linha em branco)</label>
            <textarea id="offer_privacy_summary" name="offer_privacy_summary" class="form-input" rows="8" maxlength="6000"><?= h((string) $offer['privacy_summary']) ?></textarea>
          </div>
          <div class="form-group">
            <label for="offer_privacy_url">URL da página completa</label>
            <input id="offer_privacy_url" name="offer_privacy_url" class="form-input" maxlength="160" value="<?= h((string) $offer['privacy_url']) ?>">
          </div>

          <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">Salvar oferta pública</button>
        </form>
      </section>
      <?php endif; ?>

      <?php if ($isAgencyAdmin): ?>
      <section class="admin-perm" style="margin-top:1.75rem;">
        <h2 class="font-display" style="font-size:1.35rem;margin:0 0 .35rem;">Permissões (só Admin)</h2>
        <p class="text-muted" style="margin:0 0 1rem;font-size:.875rem;">
          Controle o que cada papel vê no painel e o que cada plano libera para o cliente.
          Admin sempre tem acesso total.
        </p>

        <div class="admin-perm__tabs" role="tablist">
          <button type="button" class="admin-perm__tab is-active" data-perm-tab="roles">Papéis × páginas</button>
          <button type="button" class="admin-perm__tab" data-perm-tab="plans">Planos × recursos</button>
        </div>

        <form method="post" class="contact-form admin-form admin-form--wide">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save_permissions">

          <div class="admin-perm__panel is-active" data-perm-panel="roles">
            <p class="text-muted" style="font-size:.8rem;margin:0 0 .75rem;">
              Marque as áreas do painel liberadas para Editor e clientes. Use o filtro para achar rápido.
            </p>
            <div class="form-group" style="max-width:16rem;margin-bottom:.75rem;">
              <label for="perm-filter">Filtrar páginas</label>
              <input type="search" id="perm-filter" class="form-input" placeholder="ex.: marca, senha…" autocomplete="off">
            </div>
            <div class="admin-perm-table-wrap">
              <table class="admin-perm-table">
                <thead>
                  <tr>
                    <th>Página</th>
                    <?php foreach ($roleLabels as $rid => $rlabel): ?>
                      <th><?= h($rlabel) ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                <?php
                $lastGroup = '';
                foreach ($pageCatalog as $pageKey => $meta):
                    $group = (string) $meta['group'];
                    if ($group !== $lastGroup):
                        $lastGroup = $group;
                ?>
                  <tr class="admin-perm-table__group" data-perm-group="<?= h($group) ?>">
                    <td colspan="4"><?= h($group) ?></td>
                  </tr>
                <?php endif; ?>
                  <tr data-perm-row="<?= h(strtolower($meta['label'] . ' ' . $pageKey)) ?>">
                    <td><?= h($meta['label']) ?></td>
                    <?php foreach ($roleLabels as $rid => $_rlabel): ?>
                      <td>
                        <label class="admin-perm-check">
                          <input type="checkbox"
                            name="perm_role[<?= h($rid) ?>][]"
                            value="<?= h($pageKey) ?>"
                            <?= in_array($pageKey, $rolePages[$rid], true) ? 'checked' : '' ?>>
                        </label>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="admin-perm__panel" data-perm-panel="plans" hidden>
            <p class="text-muted" style="font-size:.8rem;margin:0 0 .75rem;">
              Recursos liberados por plano (intersectam com o papel Cliente). Basic não permite login.
            </p>
            <div class="admin-perm-table-wrap">
              <table class="admin-perm-table">
                <thead>
                  <tr>
                    <th>Recurso</th>
                    <?php foreach ($labels as $pid => $plabel): ?>
                      <th><?= h($plabel) ?></th>
                    <?php endforeach; ?>
                  </tr>
                </thead>
                <tbody>
                <?php
                $lastGroup = '';
                foreach ($featCatalog as $featKey => $meta):
                    $group = (string) $meta['group'];
                    if ($group !== $lastGroup):
                        $lastGroup = $group;
                ?>
                  <tr class="admin-perm-table__group">
                    <td colspan="4"><?= h($group) ?></td>
                  </tr>
                <?php endif; ?>
                  <tr>
                    <td><?= h($meta['label']) ?></td>
                    <?php foreach ($labels as $pid => $_plabel): ?>
                      <td>
                        <label class="admin-perm-check">
                          <input type="checkbox"
                            name="perm_plan[<?= h($pid) ?>][<?= h($featKey) ?>]"
                            value="1"
                            <?= !empty($planFeats[$pid][$featKey]) ? 'checked' : '' ?>
                            <?= ($pid === 'basic' && $featKey === 'login') ? 'disabled' : '' ?>>
                        </label>
                      </td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="admin-perm__actions">
            <button type="submit" class="btn btn-primary">Salvar permissões</button>
          </div>
        </form>

        <form method="post" style="margin-top:.75rem;" onsubmit="return confirm('Restaurar padrões de Editor / Clientes / planos?');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="reset_permissions">
          <button type="submit" class="btn btn-outline">Restaurar padrões</button>
        </form>
      </section>
      <?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
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
        <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
        <input type="hidden" name="action" value="save">

        <div class="form-group">
          <label for="site_plan">Plano contratado</label>
          <select id="site_plan" name="site_plan" class="form-input">
            <?php foreach ($labels as $id => $label): ?>
              <option value="<?= h($id) ?>" <?= ($values['site_plan'] ?? '') === $id ? 'selected' : '' ?>><?= h($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <h2 class="admin-appearance__label" style="margin-top:1.25rem;">Páginas do menu</h2>
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
          <label for="limit_gallery">Máx. imagens na galeria</label>
          <input id="limit_gallery" name="limit_gallery" class="form-input" maxlength="3" value="<?= h($values['limit_gallery'] ?? '12') ?>">
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar plano e flags</button>
      </form>

<?php if ($isAgencyAdmin): ?>
<script>
(function () {
  var tabs = document.querySelectorAll('[data-perm-tab]');
  var panels = document.querySelectorAll('[data-perm-panel]');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var id = tab.getAttribute('data-perm-tab');
      tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
      panels.forEach(function (p) {
        var on = p.getAttribute('data-perm-panel') === id;
        p.hidden = !on;
        p.classList.toggle('is-active', on);
      });
    });
  });
  var filter = document.getElementById('perm-filter');
  if (filter) {
    filter.addEventListener('input', function () {
      var q = (filter.value || '').toLowerCase().trim();
      document.querySelectorAll('[data-perm-row]').forEach(function (row) {
        var hay = row.getAttribute('data-perm-row') || '';
        row.style.display = !q || hay.indexOf(q) !== -1 ? '' : 'none';
      });
    });
  }
})();
</script>
<?php endif; ?>
<?php
admin_footer();
