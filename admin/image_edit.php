<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/settings.php';
require_once dirname(__DIR__) . '/lib/plans.php';
require_once dirname(__DIR__) . '/lib/uploads.php';

auth_boot_session();
$user = require_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$image = null;
if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM images WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $image = $stmt->fetch() ?: null;
    if (!$image) {
        header('Location: index.php');
        exit;
    }
}

$error = '';
$settings = settings_all(db());
$limitGallery = plan_limit_int($settings, 'limit_gallery', 12);

function normalize_slug(string $slug): string
{
    $slug = strtolower(trim($slug));
    $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug) ?? '';
    return trim($slug, '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $title = trim((string) ($_POST['title'] ?? ''));
    $slug = normalize_slug((string) ($_POST['slug'] ?? ''));
    $url = trim((string) ($_POST['url'] ?? ''));
    $description = trim(str_replace(['<', '>'], '', (string) ($_POST['description'] ?? '')));
    $price = trim(str_replace(['<', '>'], '', (string) ($_POST['price'] ?? '')));
    $promoPrice = trim(str_replace(['<', '>'], '', (string) ($_POST['promo_price'] ?? '')));
    if (function_exists('mb_substr')) {
        $description = mb_substr($description, 0, 250, 'UTF-8');
        $price = mb_substr($price, 0, 20, 'UTF-8');
        $promoPrice = mb_substr($promoPrice, 0, 20, 'UTF-8');
        $title = mb_substr($title, 0, 120, 'UTF-8');
    } else {
        $description = substr($description, 0, 250);
        $price = substr($price, 0, 20);
        $promoPrice = substr($promoPrice, 0, 20);
        $title = substr($title, 0, 120);
    }
    $position = (int) ($_POST['position'] ?? 0);
    $active = isset($_POST['active']) ? 1 : 0;
    $now = gmdate('c');
    $pdo = db();

    if (!empty($_FILES['file']['name'])) {
        $up = uploads_handle($_FILES['file'], $slug !== '' ? $slug : 'file');
        if (!$up['ok']) {
            $error = $up['error'] ?? 'Falha no upload.';
        } else {
            $url = (string) $up['path'];
        }
    }

    if ($error === '' && ($title === '' || $slug === '' || $url === '')) {
        $error = 'Preencha título, slug e URL — ou envie um arquivo.';
    }

    if ($error === '') {
        $reserved = ['favicon', 'logo', 'hero', 'casal'];
        $isReserved = in_array($slug, $reserved, true) || str_starts_with($slug, 'video-');
        if ($active === 1 && !$isReserved) {
            $countStmt = $pdo->query(
                "SELECT COUNT(*) FROM images WHERE active = 1 AND slug NOT IN ('favicon','logo','hero','casal') AND slug NOT LIKE 'video-%'"
            );
            $galleryCount = (int) $countStmt->fetchColumn();
            $wasCounted = false;
            if ($image && (int) ($image['active'] ?? 0) === 1) {
                $oldSlug = (string) ($image['slug'] ?? '');
                if (!in_array($oldSlug, $reserved, true) && !str_starts_with($oldSlug, 'video-')) {
                    $wasCounted = true;
                }
            }
            $would = $wasCounted ? $galleryCount : $galleryCount + 1;
            if ($would > $limitGallery) {
                $error = "Plano permite no máximo {$limitGallery} imagens na galeria (além de logo/favicon/hero/casal/vídeos).";
            }
        }
    }

    if ($error === '') {
        try {
            if ($image) {
                $stmt = $pdo->prepare(
                    'UPDATE images
                     SET url = :url, title = :title, slug = :slug, description = :description,
                         price = :price, promo_price = :promo_price, position = :position,
                         active = :active, updated_at = :updated_at
                     WHERE id = :id'
                );
                $stmt->execute([
                    ':url' => $url,
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':price' => $price,
                    ':promo_price' => $promoPrice,
                    ':position' => $position,
                    ':active' => $active,
                    ':updated_at' => $now,
                    ':id' => (int) $image['id'],
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO images (url, title, slug, description, price, promo_price, position, active, created_at, updated_at)
                     VALUES (:url, :title, :slug, :description, :price, :promo_price, :position, :active, :created_at, :updated_at)'
                );
                $stmt->execute([
                    ':url' => $url,
                    ':title' => $title,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':price' => $price,
                    ':promo_price' => $promoPrice,
                    ':position' => $position,
                    ':active' => $active,
                    ':created_at' => $now,
                    ':updated_at' => $now,
                ]);
            }
            header('Location: index.php?ok=' . urlencode('Imagem salva.'));
            exit;
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), 'UNIQUE')) {
                $error = 'Já existe uma imagem com este slug.';
            } else {
                $error = 'Erro ao salvar: ' . $e->getMessage();
            }
            $image = [
                'id' => $image['id'] ?? 0,
                'title' => $title,
                'slug' => $slug,
                'url' => $url,
                'description' => $description,
                'price' => $price,
                'promo_price' => $promoPrice,
                'position' => $position,
                'active' => $active,
            ];
        }
    } else {
        $image = [
            'id' => $image['id'] ?? 0,
            'title' => $title,
            'slug' => $slug,
            'url' => $url,
            'description' => $description,
            'price' => $price,
            'promo_price' => $promoPrice,
            'position' => $position,
            'active' => $active,
        ];
    }
}

$isEdit = $image !== null && (int) ($image['id'] ?? 0) > 0;
$defaultPosition = $isEdit ? (int) $image['position'] : (int) db()->query('SELECT COALESCE(MAX(position), -1) + 1 FROM images')->fetchColumn();

admin_header($isEdit ? 'Editar imagem' : 'Nova imagem', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Imagens</p>
        <h1 class="font-display"><?= $isEdit ? 'Editar imagem' : 'Nova imagem' ?></h1>
        <p>Envie arquivo para <code>assets/uploads/</code> (não entra no SQLite) ou cole uma URL. Slots: <code>logo</code>, <code>favicon</code>, <code>hero</code>, <code>casal</code>, vídeos <code>video-home</code> / <code>video-sobre</code> / <code>video-galeria</code> / <code>video-contato</code>.</p>
      </header>

      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="contact-form admin-form" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="title">Título</label>
          <input id="title" name="title" class="form-input" required value="<?= h((string) ($image['title'] ?? '')) ?>">
        </div>
        <div class="form-group">
          <label for="slug">Slug</label>
          <input id="slug" name="slug" class="form-input" required pattern="[a-z0-9\-]+" value="<?= h((string) ($image['slug'] ?? '')) ?>">
        </div>
        <div class="form-group">
          <label for="file">Arquivo (upload)</label>
          <input id="file" name="file" type="file" class="form-input" accept=".jpg,.jpeg,.png,.webp,.svg,.gif,.mp4,.webm,image/*,video/mp4,video/webm">
          <p class="text-muted" style="margin:0.35rem 0 0;font-size:0.8rem;">jpg, png, webp, svg, mp4, webm — até 40 MB. O path relativo é salvo no banco.</p>
        </div>
        <div class="form-group">
          <label for="url">URL ou path (se não enviar arquivo)</label>
          <input id="url" name="url" class="form-input" value="<?= h((string) ($image['url'] ?? '')) ?>" placeholder="assets/uploads/... ou https://...">
        </div>
        <div class="form-group">
          <label for="description">Descrição (galeria) <span class="admin-charlimit" data-for="description">0/250</span></label>
          <textarea id="description" name="description" class="form-input" rows="3" maxlength="250" placeholder="Opcional — aparece ao ampliar a foto"><?= h((string) ($image['description'] ?? '')) ?></textarea>
        </div>
        <div class="form-group">
          <label for="price">Preço <span class="admin-charlimit" data-for="price">0/20</span></label>
          <input id="price" name="price" class="form-input" maxlength="20" value="<?= h((string) ($image['price'] ?? '')) ?>" placeholder="Ex.: R$ 89,00 — deixe vazio para ocultar">
        </div>
        <div class="form-group">
          <label for="promo_price">Preço promocional <span class="admin-charlimit" data-for="promo_price">0/20</span></label>
          <input id="promo_price" name="promo_price" class="form-input" maxlength="20" value="<?= h((string) ($image['promo_price'] ?? '')) ?>" placeholder="Ex.: R$ 69,00 — risca o preço acima">
        </div>
        <div class="form-group">
          <label for="position">Ordem (position)</label>
          <input id="position" name="position" type="number" class="form-input" value="<?= (int) ($image['position'] ?? $defaultPosition) ?>">
        </div>
        <label class="admin-check">
          <input type="checkbox" name="active" value="1" <?= ((int) ($image['active'] ?? 1) === 1) ? 'checked' : '' ?>>
          Ativa no site
        </label>
        <?php if (!empty($image['url'])): ?>
          <?php
            $preview = (string) $image['url'];
            $isVideo = (bool) preg_match('/\.(mp4|webm)(\?|$)/i', $preview);
          ?>
          <?php if ($isVideo): ?>
            <video class="admin-preview" src="<?= h($preview) ?>" controls muted playsinline style="max-width:100%;max-height:220px;"></video>
          <?php else: ?>
            <img class="admin-preview" src="<?= h($preview) ?>" alt="" referrerpolicy="no-referrer">
          <?php endif; ?>
        <?php endif; ?>
        <div style="display:flex;gap:0.75rem;margin-top:1.5rem;flex-wrap:wrap;">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <a href="index.php" class="btn btn-outline">Cancelar</a>
        </div>
      </form>
      <script src="admin-limits.js"></script>
<?php
admin_footer();
