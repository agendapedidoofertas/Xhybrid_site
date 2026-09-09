<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/services.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/plans.php';

auth_boot_session();
$user = require_admin();

$pdo = db();
$flash = '';
$error = '';
$edit = null;
$settings = settings_all($pdo);
$limitServices = plan_limit_int($settings, 'limit_services', 6);
$activeCount = (int) $pdo->query('SELECT COUNT(*) FROM services WHERE active = 1')->fetchColumn();

if (isset($_GET['id'])) {
    $edit = services_get($pdo, (int) $_GET['id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? 'save');
    try {
        if ($action === 'delete') {
            services_delete($pdo, (int) ($_POST['id'] ?? 0));
            header('Location: services.php?ok=deleted');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $willActive = isset($_POST['active']);
        if ($willActive) {
            $would = $activeCount;
            if ($id > 0) {
                $cur = services_get($pdo, $id);
                if ($cur && (int) $cur['active'] === 1) {
                    $would = $activeCount;
                } else {
                    $would = $activeCount + 1;
                }
            } else {
                $would = $activeCount + 1;
            }
            if ($would > $limitServices) {
                throw new RuntimeException("Plano permite no máximo {$limitServices} serviços ativos.");
            }
        }
        services_save($pdo, [
            'title' => (string) ($_POST['title'] ?? ''),
            'description' => (string) ($_POST['description'] ?? ''),
            'image_slug' => (string) ($_POST['image_slug'] ?? ''),
            'category' => (string) ($_POST['category'] ?? ''),
            'position' => (int) ($_POST['position'] ?? 0),
            'active' => $willActive,
        ], $id > 0 ? $id : null);
        header('Location: services.php?ok=1');
        exit;
    } catch (Throwable $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

if (isset($_GET['ok'])) {
    $flash = $_GET['ok'] === 'deleted' ? 'Serviço excluído.' : 'Serviço salvo.';
}

$services = services_list($pdo, false);

admin_header('Serviços', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Site</p>
        <h1 class="font-display">Serviços</h1>
        <p>Cards de serviços/trabalhos da home e destaques. Limite do plano: <strong><?= (int) $limitServices ?></strong> ativos (agora <?= (int) $activeCount ?>).</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <section class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <h2 style="margin:0 0 1rem;font-size:1.1rem;"><?= $edit ? 'Editar serviço' : 'Novo serviço' ?></h2>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
          <div class="form-group">
            <label for="title">Título</label>
            <input id="title" name="title" class="form-input" maxlength="80" required value="<?= h((string) ($edit['title'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label for="description">Descrição</label>
            <textarea id="description" name="description" class="form-input" rows="2" maxlength="220"><?= h((string) ($edit['description'] ?? '')) ?></textarea>
          </div>
          <div class="form-group">
            <label for="category">Categoria</label>
            <input id="category" name="category" class="form-input" maxlength="40" value="<?= h((string) ($edit['category'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label for="image_slug">Slug da imagem</label>
            <input id="image_slug" name="image_slug" class="form-input" maxlength="40" placeholder="amigurumi" value="<?= h((string) ($edit['image_slug'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label for="position">Ordem</label>
            <input id="position" name="position" type="number" class="form-input" value="<?= (int) ($edit['position'] ?? 0) ?>">
          </div>
          <label class="admin-check">
            <input type="checkbox" name="active" value="1" <?= !isset($edit['active']) || (int) $edit['active'] === 1 ? 'checked' : '' ?>>
            Ativo no site
          </label>
          <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:1rem;">
            <button type="submit" class="btn btn-primary"><?= $edit ? 'Atualizar' : 'Criar' ?></button>
            <?php if ($edit): ?>
              <a class="btn btn-outline" href="services.php">Cancelar</a>
            <?php endif; ?>
          </div>
        </form>
      </section>

      <div class="admin-table-wrap" style="margin-top:2rem;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Ordem</th>
              <th>Título</th>
              <th>Slug img</th>
              <th>Ativo</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($services as $s): ?>
              <tr>
                <td><?= (int) $s['position'] ?></td>
                <td><?= h((string) $s['title']) ?></td>
                <td><code><?= h((string) $s['image_slug']) ?></code></td>
                <td><?= (int) $s['active'] === 1 ? 'sim' : 'não' ?></td>
                <td>
                  <div class="admin-row-actions">
                    <a class="btn btn-outline" style="padding:0.35rem 0.7rem;font-size:0.8rem;" href="services.php?id=<?= (int) $s['id'] ?>">Editar</a>
                    <form method="post" onsubmit="return confirm('Excluir este serviço?');">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                      <button type="submit" class="btn btn-outline" style="padding:0.35rem 0.7rem;font-size:0.8rem;">Excluir</button>
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
