<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';
require_once dirname(__DIR__) . '/lib/plans.php';
require_once dirname(__DIR__) . '/lib/permissions.php';

auth_boot_session();
$user = require_role_admin();

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');
        $role = (string) ($_POST['role'] ?? 'editor');
        $crmLeadId = (int) ($_POST['crm_lead_id'] ?? 0);
        $crmLeadId = $crmLeadId > 0 ? $crmLeadId : null;

        if ($username === '' || strlen($username) < 3) {
            $error = 'Usuário com pelo menos 3 caracteres.';
        } elseif (strlen($password) < 8) {
            $error = 'A senha precisa ter pelo menos 8 caracteres.';
        } elseif ($password !== $confirm) {
            $error = 'A confirmação de senha não confere.';
        } elseif (($role === 'client_medium' || $role === 'client_pro') && $crmLeadId === null) {
            $error = 'Informe o lead_id do CRM para usuários cliente.';
        } else {
            try {
                if ($role === 'client_medium' || $role === 'client_pro') {
                    $site = published_site_get_by_lead(db(), (int) $crmLeadId);
                    if (!$site) {
                        throw new InvalidArgumentException('Lead #' . $crmLeadId . ' sem site publicado.');
                    }
                    $tier = plan_normalize((string) ($site['plan_tier'] ?? 'basic'));
                    if ($tier === 'basic') {
                        throw new InvalidArgumentException('Plano Basic não possui login de cliente.');
                    }
                    if (!permissions_plan_allows(db(), $tier, 'login')) {
                        throw new InvalidArgumentException('Este plano está com login de cliente desligado na matriz de permissões.');
                    }
                    if ($role === 'client_medium' && $tier !== 'medium') {
                        throw new InvalidArgumentException('client_medium exige plano Medium no lead.');
                    }
                    if ($role === 'client_pro' && $tier !== 'pro') {
                        throw new InvalidArgumentException('client_pro exige plano Pro no lead.');
                    }
                }
                create_user($username, $password, $role, $crmLeadId);
                $ok = 'Usuário criado.';
            } catch (Throwable $e) {
                $error = str_contains($e->getMessage(), 'UNIQUE')
                    ? 'Este nome de usuário já existe.'
                    : $e->getMessage();
            }
        }
    } elseif ($action === 'reset') {
        $targetId = (int) ($_POST['id'] ?? 0);
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['confirm'] ?? '');
        if ($password !== $confirm) {
            $error = 'A confirmação de senha não confere.';
        } else {
            $error = admin_reset_password($targetId, $password);
            if ($error === '') {
                $ok = 'Senha redefinida.';
            }
        }
    } elseif ($action === 'delete') {
        $targetId = (int) ($_POST['id'] ?? 0);
        $error = delete_user_by_id($targetId, (int) $user['id']);
        if ($error === '') {
            $ok = 'Usuário excluído.';
        }
    }
}

$users = list_users();
$leads = published_site_list(db());

admin_header('Usuários', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Acesso</p>
        <h1 class="font-display">Usuários</h1>
        <p>Staff (admin/editor) e clientes Medium/Pro vinculados a um lead. Basic não cria login de cliente.</p>
      </header>

      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <?php if ($ok): ?><p class="admin-flash"><?= h($ok) ?></p><?php endif; ?>

      <section class="contact-form admin-form" style="margin-top:1.5rem;">
        <h2 class="font-display" style="font-size:1.5rem;">Novo usuário</h2>
        <form method="post" style="margin-top:1rem;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="create">
          <div class="form-group">
            <label for="username">Usuário</label>
            <input id="username" name="username" class="form-input" required minlength="3" autocomplete="off">
          </div>
          <div class="form-group">
            <label for="password">Senha inicial</label>
            <input id="password" name="password" type="password" class="form-input" required minlength="8" autocomplete="new-password">
          </div>
          <div class="form-group">
            <label for="confirm">Confirmar senha</label>
            <input id="confirm" name="confirm" type="password" class="form-input" required minlength="8" autocomplete="new-password">
          </div>
          <div class="form-group form-group--role-help">
            <label for="role" class="admin-label-with-help">
              Tipo
              <button type="button" class="admin-help-btn" id="role-help-btn" aria-expanded="false" aria-controls="role-help-panel" title="Ajuda deste tipo">
                <span class="admin-help-btn__mark" aria-hidden="true">?</span>
                <span class="admin-sr-only">Ajuda sobre o tipo selecionado</span>
              </button>
            </label>
            <select id="role" name="role" class="form-input">
              <option value="editor">Editor (staff)</option>
              <option value="admin">Admin (staff)</option>
              <option value="client_medium">Cliente Medium</option>
              <option value="client_pro">Cliente Pro</option>
            </select>
            <div class="admin-help-tour" id="role-help-panel" hidden>
              <p class="admin-help-tour__title" id="role-help-title">Editor (staff)</p>
              <p class="admin-help-tour__body" id="role-help-body">Staff: edita vitrine e leads. Sem gestão.</p>
              <p class="admin-help-tour__note">Basic não gera login de cliente. Cliente só o lead vinculado.</p>
            </div>
          </div>
          <div class="form-group">
            <label for="crm_lead_id">Lead CRM (obrigatório para cliente)</label>
            <select id="crm_lead_id" name="crm_lead_id" class="form-input">
              <option value="">—</option>
              <?php foreach ($leads as $lead): ?>
                <option value="<?= (int) $lead['crm_lead_id'] ?>">
                  #<?= (int) $lead['crm_lead_id'] ?> — <?= h((string) $lead['company_name']) ?> (<?= h(plan_normalize((string) ($lead['plan_tier'] ?? 'basic'))) ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Criar usuário</button>
        </form>
      </section>

      <div class="admin-table-wrap" style="margin-top:2.5rem;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Usuário</th>
              <th>Tipo</th>
              <th>Lead</th>
              <th>Criado</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><strong><?= h($u['username']) ?></strong><?= (int) $u['id'] === (int) $user['id'] ? ' <span class="text-muted">(você)</span>' : '' ?></td>
              <td><?= h($u['role'] ?? 'admin') ?></td>
              <td><?= !empty($u['crm_lead_id']) ? '#' . (int) $u['crm_lead_id'] : '—' ?></td>
              <td class="text-muted"><?= h((string) ($u['created_at'] ?? '')) ?></td>
              <td>
                <div class="admin-row-actions" style="flex-direction:column;align-items:flex-start;gap:0.75rem;">
                  <form method="post" style="display:flex;flex-wrap:wrap;gap:0.4rem;align-items:flex-end;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="reset">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <input type="password" name="password" class="form-input" placeholder="Nova senha" required minlength="8" style="width:9rem;margin:0;" autocomplete="new-password">
                    <input type="password" name="confirm" class="form-input" placeholder="Confirmar" required minlength="8" style="width:9rem;margin:0;" autocomplete="new-password">
                    <button type="submit" class="btn btn-outline btn-sm">Redefinir senha</button>
                  </form>
                  <?php if ((int) $u['id'] !== (int) $user['id']): ?>
                  <form method="post" onsubmit="return confirm('Excluir este usuário?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                    <button type="submit" class="btn btn-outline btn-sm">Excluir</button>
                  </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
<script>
(function () {
  var btn = document.getElementById('role-help-btn');
  var panel = document.getElementById('role-help-panel');
  var select = document.getElementById('role');
  var titleEl = document.getElementById('role-help-title');
  var bodyEl = document.getElementById('role-help-body');
  if (!btn || !panel || !select || !titleEl || !bodyEl) return;

  var tips = {
    editor: {
      title: 'Editor (staff)',
      body: 'Staff: edita vitrine e leads. Sem gestão.'
    },
    admin: {
      title: 'Admin (staff)',
      body: 'Staff total: usuários, backup, plano, marca…'
    },
    client_medium: {
      title: 'Cliente Medium',
      body: 'Só o próprio site (contato/textos). Sem Marca.'
    },
    client_pro: {
      title: 'Cliente Pro',
      body: 'Medium + aparência, preset, marca, seções.'
    }
  };

  function syncTip() {
    var tip = tips[select.value] || tips.editor;
    titleEl.textContent = tip.title;
    bodyEl.textContent = tip.body;
  }

  function closeHelp() {
    panel.hidden = true;
    btn.setAttribute('aria-expanded', 'false');
  }

  function openHelp() {
    syncTip();
    panel.hidden = false;
    btn.setAttribute('aria-expanded', 'true');
  }

  syncTip();

  btn.addEventListener('click', function (e) {
    e.preventDefault();
    e.stopPropagation();
    if (panel.hidden) openHelp();
    else closeHelp();
  });

  select.addEventListener('change', function () {
    syncTip();
    if (!panel.hidden) openHelp();
  });

  document.addEventListener('click', function (e) {
    if (!panel.hidden && !panel.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
      closeHelp();
    }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeHelp();
  });
})();
</script>
<?php
admin_footer();
