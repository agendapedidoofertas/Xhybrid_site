<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/plans.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';
require_once dirname(__DIR__) . '/lib/brand_mark.php';

auth_boot_session();
$user = require_page('leads');
if (user_is_client($user)) {
    $own = user_crm_lead_id($user);
    header('Location: ' . ($own ? ('lead_hub.php?lead_id=' . $own) : 'index.php'));
    exit;
}

$planFilter = strtolower(trim((string) ($_GET['plan_tier'] ?? '')));
if (!in_array($planFilter, ['basic', 'medium', 'pro', ''], true)) {
    $planFilter = '';
}
$activeFilter = $_GET['site_active'] ?? '';
$activeOnly = null;
if ($activeFilter === '1' || $activeFilter === '0') {
    $activeOnly = (int) $activeFilter;
}

$rows = published_site_list(db(), $activeOnly, $planFilter !== '' ? $planFilter : null);
$labels = plan_labels();
$blurbs = plan_blurbs();

admin_header('Leads (sites)', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><a href="index.php">← Painel</a> · CRM → Xhybrid</p>
        <h1 class="font-display">Sites de leads</h1>
        <p>Edite contato, aparência e textos de cada site publicado. A vitrine da agência continua em Contato / Textos / Aparência.</p>
      </header>

      <form method="get" class="admin-form" style="margin-top:1.25rem;display:flex;flex-wrap:wrap;gap:.75rem;align-items:end;">
        <div class="form-group" style="margin:0;min-width:10rem;">
          <label for="plan_tier">Plano</label>
          <select id="plan_tier" name="plan_tier" class="form-input">
            <option value="">Todos</option>
            <?php foreach ($labels as $pid => $plabel): ?>
              <option value="<?= h($pid) ?>" <?= $planFilter === $pid ? 'selected' : '' ?>><?= h($plabel) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group" style="margin:0;min-width:10rem;">
          <label for="site_active">Status</label>
          <select id="site_active" name="site_active" class="form-input">
            <option value="">Todos</option>
            <option value="1" <?= (string) $activeFilter === '1' ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= (string) $activeFilter === '0' ? 'selected' : '' ?>>Inativo</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary" style="margin:0;">Filtrar</button>
        <p class="text-muted" style="margin:0;font-size:.85rem;">
          <a href="leads.php?plan_tier=basic">Basic</a> ·
          <a href="leads.php?plan_tier=medium">Medium</a> ·
          <a href="leads.php?plan_tier=pro">Pro</a>
        </p>
      </form>

      <?php if ($rows === []): ?>
        <p class="admin-flash" style="margin-top:1.5rem;">Nenhum site de lead<?= $planFilter !== '' ? ' neste plano' : '' ?>. Ative um lead no CRM para aparecer aqui.</p>
      <?php else: ?>
      <div class="admin-table-wrap" style="margin-top:1.5rem;overflow-x:auto;">
        <table class="admin-table" style="width:100%;border-collapse:collapse;font-size:0.9rem;">
          <thead>
            <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,.12);">
              <th style="padding:.5rem;">ID</th>
              <th style="padding:.5rem;">Empresa</th>
              <th style="padding:.5rem;">Plano</th>
              <th style="padding:.5rem;">Cidade</th>
              <th style="padding:.5rem;">Status</th>
              <th style="padding:.5rem;">URL</th>
              <th style="padding:.5rem;"></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <?php
                $path = published_site_public_path($row);
                $active = (int) ($row['site_active'] ?? 0) === 1;
                $tier = plan_normalize((string) ($row['plan_tier'] ?? 'basic'));
              ?>
              <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                <td style="padding:.5rem;"><?= (int) $row['crm_lead_id'] ?></td>
                <td style="padding:.5rem;">
                  <?= brand_mark_html((string) $row['company_name'], 'h') ?>
                  <div class="text-muted" style="font-size:.78rem;margin-top:.2rem;max-width:22rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= h((string) $row['company_name']) ?>"><?= h((string) $row['company_name']) ?></div>
                </td>
                <td style="padding:.5rem;" title="<?= h($blurbs[$tier] ?? '') ?>"><?= h($labels[$tier] ?? $tier) ?></td>
                <td style="padding:.5rem;"><?= h((string) $row['city']) ?></td>
                <td style="padding:.5rem;"><?= $active ? 'Ativo' : 'Inativo' ?></td>
                <td style="padding:.5rem;">
                  <?php if ($path !== ''): ?>
                    <a href="<?= h($path) ?>" target="_blank" rel="noopener"><?= h($path) ?></a>
                  <?php else: ?>
                    —
                  <?php endif; ?>
                </td>
                <td style="padding:.5rem;">
                  <a class="btn btn-outline" style="padding:.35rem .75rem;font-size:.8rem;" href="lead_hub.php?lead_id=<?= (int) $row['crm_lead_id'] ?>">Editar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
<?php
admin_footer();
