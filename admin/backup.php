<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';

auth_boot_session();
$user = require_role_admin();

$path = db_path();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!is_file($path)) {
        $error = 'Banco SQLite não encontrado.';
    } else {
        $filename = 'xhybrid-backup-' . gmdate('Ymd-His') . '.sqlite';
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: no-store');
        readfile($path);
        exit;
    }
}

admin_header('Backup', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Admin</p>
        <h1 class="font-display">Backup</h1>
        <p>Exporta o SQLite do site. Use POST + CSRF (não é link GET).</p>
      </header>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <?php if (!is_file($path)): ?>
        <p class="admin-flash admin-flash--error">Banco SQLite não encontrado.</p>
      <?php else: ?>
        <form method="post" class="contact-form admin-form" style="margin-top:1.25rem;max-width:28rem;">
          <?= csrf_field() ?>
          <p class="text-muted" style="font-size:0.9rem;">Arquivo: <code><?= h(basename($path)) ?></code></p>
          <button type="submit" class="btn btn-primary">Baixar backup</button>
        </form>
      <?php endif; ?>
<?php
admin_footer();
