<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/images.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';

auth_boot_session();

if (user_count() === 0) {
    header('Location: setup.php');
    exit;
}

$user = require_page('images');
$isAdmin = user_is_admin($user);
$isStaff = user_is_staff($user);
$canManage = $isAdmin || user_is_client($user);

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
    $canManage = true;
} elseif (user_is_client($user)) {
    $own = user_crm_lead_id($user);
    if ($own) {
        header('Location: images.php?lead_id=' . $own);
        exit;
    }
}

$flash = '';
$qs = $leadId ? ('?' . lead_admin_qs($leadId)) : '';

function next_position(PDO $pdo, ?int $crmLeadId): int
{
    if ($crmLeadId === null) {
        return (int) $pdo->query('SELECT COALESCE(MAX(position), -1) + 1 FROM images WHERE crm_lead_id IS NULL')->fetchColumn();
    }
    $stmt = $pdo->prepare('SELECT COALESCE(MAX(position), -1) + 1 FROM images WHERE crm_lead_id = :lead');
    $stmt->execute([':lead' => $crmLeadId]);
    return (int) $stmt->fetchColumn();
}

function swap_position(PDO $pdo, int $id, string $direction, ?int $crmLeadId): void
{
    $sql = 'SELECT id, position FROM images WHERE id = :id';
    $params = [':id' => $id];
    if ($crmLeadId === null) {
        $sql .= ' AND crm_lead_id IS NULL';
    } else {
        $sql .= ' AND crm_lead_id = :lead';
        $params[':lead'] = $crmLeadId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $current = $stmt->fetch();
    if (!$current) {
        return;
    }

    $scope = $crmLeadId === null ? 'crm_lead_id IS NULL' : 'crm_lead_id = :lead';
    if ($direction === 'up') {
        $neighbor = $pdo->prepare(
            "SELECT id, position FROM images WHERE {$scope} AND position < :pos ORDER BY position DESC LIMIT 1"
        );
    } else {
        $neighbor = $pdo->prepare(
            "SELECT id, position FROM images WHERE {$scope} AND position > :pos ORDER BY position ASC LIMIT 1"
        );
    }
    $nParams = [':pos' => (int) $current['position']];
    if ($crmLeadId !== null) {
        $nParams[':lead'] = $crmLeadId;
    }
    $neighbor->execute($nParams);
    $other = $neighbor->fetch();
    if (!$other) {
        return;
    }

    $pdo->beginTransaction();
    $upd = $pdo->prepare('UPDATE images SET position = :position, updated_at = :updated_at WHERE id = :id');
    $now = gmdate('c');
    $upd->execute([':position' => (int) $other['position'], ':updated_at' => $now, ':id' => (int) $current['id']]);
    $upd->execute([':position' => (int) $current['position'], ':updated_at' => $now, ':id' => (int) $other['id']]);
    $pdo->commit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);
    $pdo = db();

    $scopeSql = $leadId === null ? ' AND crm_lead_id IS NULL' : ' AND crm_lead_id = :lead';
    $scopeParams = $leadId === null ? [] : [':lead' => $leadId];

    if (($action === 'delete' || $action === 'toggle') && !$canManage) {
        $flash = 'Sem permissão para ativar ou excluir imagens.';
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM images WHERE id = :id' . $scopeSql);
        $stmt->execute(array_merge([':id' => $id], $scopeParams));
        $flash = 'Imagem excluída.';
    } elseif ($action === 'toggle' && $id > 0) {
        $cur = $pdo->prepare('SELECT id, url, active FROM images WHERE id = :id' . $scopeSql);
        $cur->execute(array_merge([':id' => $id], $scopeParams));
        $row = $cur->fetch();
        if ($row && (int) ($row['active'] ?? 0) === 0 && !images_url_available((string) ($row['url'] ?? ''))) {
            $flash = 'Não dá para ativar: URL vazia ou arquivo local ausente.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE images SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END, updated_at = :updated_at WHERE id = :id' . $scopeSql
            );
            $stmt->execute(array_merge([':updated_at' => gmdate('c'), ':id' => $id], $scopeParams));
            $flash = 'Status atualizado.';
        }
    } elseif (($action === 'up' || $action === 'down') && $id > 0) {
        swap_position($pdo, $id, $action, $leadId);
        $flash = 'Ordem atualizada.';
    }

    $loc = 'images.php' . ($leadId ? '?' . lead_admin_qs($leadId) . '&' : '?') . ($flash !== '' ? 'ok=' . urlencode($flash) : '');
    header('Location: ' . rtrim($loc, '?&'));
    exit;
}

$ok = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$pdo = db();
$autoOff = images_deactivate_unavailable($pdo, $leadId);
if ($autoOff > 0 && $ok === '') {
    $ok = $autoOff === 1
        ? '1 imagem sem arquivo/URL foi desativada automaticamente.'
        : "{$autoOff} imagens sem arquivo/URL foram desativadas automaticamente.";
}
$images = images_list($pdo, false, $leadId);

$editHref = static function (int $id) use ($leadId): string {
    return 'image_edit.php?' . ($leadId ? lead_admin_qs($leadId) . '&' : '') . 'id=' . $id;
};
$addHref = 'image_edit.php' . ($leadId ? '?' . lead_admin_qs($leadId) : '');

admin_header($leadId ? 'Imagens do lead' : 'Imagens', $user);
?>
      <div id="imagens" style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1rem;">
        <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
          <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Catálogo' ?></p>
          <h1 class="font-display">Imagens</h1>
          <p>Slots: <code>logo</code>, <code>favicon</code>, <code>hero</code>, <code>about</code><?= $leadId ? ' deste lead' : '' ?>.</p>
        </header>
        <a href="<?= h($addHref) ?>" class="btn btn-primary">Adicionar imagem</a>
      </div>

      <?php if ($ok !== ''): ?><p class="admin-flash"><?= h($ok) ?></p><?php endif; ?>

      <div class="admin-table-wrap">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Preview</th>
              <th>Título / slug</th>
              <th>Ordem</th>
              <th>Ativa</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$images): ?>
            <tr><td colspan="5">Nenhuma imagem cadastrada.</td></tr>
          <?php endif; ?>
          <?php foreach ($images as $img): ?>
            <tr>
              <td>
                <?php
                  $thumb = trim((string) ($img['url'] ?? ''));
                  $available = images_url_available($thumb);
                  $isActive = (int) ($img['active'] ?? 0) === 1;
                  echo admin_thumb_html($thumb, $available);
                ?>
              </td>
              <td>
                <strong><?= h($img['title']) ?></strong><br>
                <span class="text-muted"><?= h($img['slug']) ?></span>
              </td>
              <td><?= (int) $img['position'] ?></td>
              <td><?= $isActive ? 'Sim' : 'Não' ?></td>
              <td>
                <div class="admin-row-actions">
                  <a class="btn btn-outline btn-sm" href="<?= h($editHref((int) $img['id'])) ?>">Editar</a>
                  <?php if ($canManage): ?>
                  <form method="post">
                    <?= csrf_field() ?>
                    <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="btn btn-outline btn-sm"><?= $isActive ? 'Desativar' : 'Ativar' ?></button>
                  </form>
                  <?php endif; ?>
                  <form method="post">
                    <?= csrf_field() ?>
                    <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="up">
                    <button type="submit" class="btn btn-outline btn-sm" title="Subir">↑</button>
                  </form>
                  <form method="post">
                    <?= csrf_field() ?>
                    <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="down">
                    <button type="submit" class="btn btn-outline btn-sm" title="Descer">↓</button>
                  </form>
                  <?php if ($canManage): ?>
                  <form method="post" onsubmit="return confirm('Excluir esta imagem?');">
                    <?= csrf_field() ?>
                    <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="delete">
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
<?php
admin_footer();
