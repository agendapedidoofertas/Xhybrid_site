<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';

auth_boot_session();
$user = require_admin();

$rows = published_site_list(db());

admin_header('Leads (sites)', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">CRM → Xhybrid</p>
        <h1 class="font-display">Sites de leads</h1>
        <p>Edite contato, aparência e textos de cada site publicado. A vitrine da agência continua em Contato / Textos / Aparência.</p>
      </header>

      <?php if ($rows === []): ?>
        <p class="admin-flash" style="margin-top:1.5rem;">Nenhum site de lead ainda. Ative um lead no CRM para aparecer aqui.</p>
      <?php else: ?>
      <div class="admin-table-wrap" style="margin-top:1.5rem;overflow-x:auto;">
        <table class="admin-table" style="width:100%;border-collapse:collapse;font-size:0.9rem;">
          <thead>
            <tr style="text-align:left;border-bottom:1px solid rgba(255,255,255,.12);">
              <th style="padding:.5rem;">ID</th>
              <th style="padding:.5rem;">Empresa</th>
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
              ?>
              <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                <td style="padding:.5rem;"><?= (int) $row['crm_lead_id'] ?></td>
                <td style="padding:.5rem;"><?= h((string) $row['company_name']) ?></td>
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
                  <a class="btn btn-outline" style="padding:.35rem .75rem;font-size:.8rem;" href="lead_site.php?lead_id=<?= (int) $row['crm_lead_id'] ?>">Editar</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
<?php
admin_footer();
