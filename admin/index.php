<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/services.php';

auth_boot_session();

if (user_count() === 0) {
    header('Location: setup.php');
    exit;
}

$user = require_admin();
$flash = '';

function next_position(PDO $pdo): int
{
    return (int) $pdo->query('SELECT COALESCE(MAX(position), -1) + 1 FROM images')->fetchColumn();
}

function swap_position(PDO $pdo, int $id, string $direction): void
{
    $stmt = $pdo->prepare('SELECT id, position FROM images WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $current = $stmt->fetch();
    if (!$current) {
        return;
    }

    if ($direction === 'up') {
        $neighbor = $pdo->prepare(
            'SELECT id, position FROM images WHERE position < :pos ORDER BY position DESC LIMIT 1'
        );
    } else {
        $neighbor = $pdo->prepare(
            'SELECT id, position FROM images WHERE position > :pos ORDER BY position ASC LIMIT 1'
        );
    }
    $neighbor->execute([':pos' => (int) $current['position']]);
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

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM images WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $flash = 'Imagem excluída.';
    } elseif ($action === 'toggle' && $id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE images SET active = CASE WHEN active = 1 THEN 0 ELSE 1 END, updated_at = :updated_at WHERE id = :id'
        );
        $stmt->execute([':updated_at' => gmdate('c'), ':id' => $id]);
        $flash = 'Status atualizado.';
    } elseif (($action === 'up' || $action === 'down') && $id > 0) {
        swap_position($pdo, $id, $action);
        $flash = 'Ordem atualizada.';
    }

    header('Location: index.php' . ($flash !== '' ? '?ok=' . urlencode($flash) : ''));
    exit;
}

$ok = isset($_GET['ok']) ? (string) $_GET['ok'] : '';
$pdo = db();
$images = $pdo->query('SELECT * FROM images ORDER BY position ASC, id ASC')->fetchAll();
$settings = settings_all($pdo);
$servicesCount = (int) $pdo->query('SELECT COUNT(*) FROM services WHERE active = 1')->fetchColumn();
$slugs = array_map(static fn ($img) => (string) $img['slug'], $images);
$hasHero = in_array('hero', $slugs, true);
$hasLogo = in_array('logo', $slugs, true) || in_array('favicon', $slugs, true);
$hasWa = trim($settings['whatsapp_number'] ?? '') !== '' && $settings['whatsapp_number'] !== '5511999999999';
$hasBrand = trim($settings['brand_name'] ?? '') !== '' && ($settings['brand_name'] ?? '') !== 'Xhybrid';
$lookSaved = trim($settings['appearance_look'] ?? '') !== '';
$editors = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'editor'")->fetchColumn();
$planLabel = $settings['site_plan'] ?? 'profissional';

admin_header('Painel', $user);
?>
      <?php if (user_is_admin($user)): ?>
      <section class="contact-form admin-form admin-form--wide" style="margin-bottom:2rem;">
        <h2 class="font-display" style="font-size:1.15rem;margin:0 0 0.75rem;">Checklist de entrega</h2>
        <p class="text-muted" style="margin:0 0 0.75rem;">Plano atual: <strong><?= h($planLabel) ?></strong> · <a href="plan.php">alterar</a></p>
        <ul class="admin-checklist">
          <li class="<?= $hasBrand ? 'is-done' : '' ?>"><?= $hasBrand ? '✓' : '○' ?> Marca personalizada (não Xhybrid)</li>
          <li class="<?= $hasWa ? 'is-done' : '' ?>"><?= $hasWa ? '✓' : '○' ?> WhatsApp real cadastrado</li>
          <li class="<?= $hasHero ? 'is-done' : '' ?>"><?= $hasHero ? '✓' : '○' ?> Imagem <code>hero</code></li>
          <li class="<?= $hasLogo ? 'is-done' : '' ?>"><?= $hasLogo ? '✓' : '○' ?> <code>logo</code> ou <code>favicon</code></li>
          <li class="<?= $servicesCount >= 3 ? 'is-done' : '' ?>"><?= $servicesCount >= 3 ? '✓' : '○' ?> Pelo menos 3 serviços ativos</li>
          <li class="<?= $lookSaved ? 'is-done' : '' ?>"><?= $lookSaved ? '✓' : '○' ?> Look de aparência definido</li>
          <li class="<?= $editors > 0 ? 'is-done' : '' ?>"><?= $editors > 0 ? '✓' : '○' ?> Usuário editor (dono do site) criado</li>
        </ul>
        <p class="text-muted" style="margin:0.75rem 0 0;font-size:0.85rem;">
          Atalhos:
          <a href="plan.php">Plano</a> ·
          <a href="preset.php">Preset</a> ·
          <a href="brand.php">Marca</a> ·
          <a href="appearance.php">Aparência</a> ·
          <a href="users.php">Usuários</a>
        </p>
        <p class="text-muted" style="margin:0.5rem 0 0;font-size:0.85rem;">
          <a href="backup.php">Baixar backup do SQLite</a>
          · mídia fica em <code>assets/uploads/</code> (não no banco)
        </p>
      </section>
      <?php endif; ?>

      <div id="imagens" style="display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:1rem;">
        <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
          <p class="eyebrow">Catálogo</p>
          <h1 class="font-display">Imagens</h1>
          <p>Slots fixos: <code>logo</code>, <code>favicon</code>, <code>hero</code>, <code>casal</code> + vídeos <code>video-home</code> / <code>video-sobre</code> / <code>video-galeria</code> / <code>video-contato</code>. Demais entram na galeria.</p>
        </header>
        <a href="image_edit.php" class="btn btn-primary">Adicionar imagem</a>
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
                <?php if ($img['url'] !== ''): ?>
                  <img src="<?= h($img['url']) ?>" alt="" referrerpolicy="no-referrer" onerror="this.style.opacity=0.3">
                <?php endif; ?>
              </td>
              <td>
                <strong><?= h($img['title']) ?></strong><br>
                <span class="text-muted"><?= h($img['slug']) ?></span>
              </td>
              <td><?= (int) $img['position'] ?></td>
              <td><?= ((int) $img['active'] === 1) ? 'Sim' : 'Não' ?></td>
              <td>
                <div class="admin-row-actions">
                  <a class="btn btn-outline btn-sm" href="image_edit.php?id=<?= (int) $img['id'] ?>">Editar</a>
                  <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="toggle">
                    <button type="submit" class="btn btn-outline btn-sm"><?= ((int) $img['active'] === 1) ? 'Desativar' : 'Ativar' ?></button>
                  </form>
                  <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="up">
                    <button type="submit" class="btn btn-outline btn-sm" title="Subir">↑</button>
                  </form>
                  <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="down">
                    <button type="submit" class="btn btn-outline btn-sm" title="Descer">↓</button>
                  </form>
                  <form method="post" onsubmit="return confirm('Excluir esta imagem?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $img['id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-outline btn-sm">Excluir</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
<?php
admin_footer();
