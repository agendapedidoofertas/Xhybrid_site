<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';

auth_boot_session();
$user = require_role_admin();

$path = db_path();
if (!is_file($path)) {
    admin_header('Backup', $user);
    echo '<p class="admin-flash admin-flash--error">Banco SQLite não encontrado.</p>';
    admin_footer();
    exit;
}

$filename = 'xhybrid-backup-' . gmdate('Ymd-His') . '.sqlite';
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . (string) filesize($path));
header('Cache-Control: no-store');
readfile($path);
exit;
