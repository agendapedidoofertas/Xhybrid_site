<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';

$src = db_path();
if (!is_file($src)) {
    fwrite(STDERR, "SQLite não encontrado em {$src}\n");
    exit(1);
}

$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'backups';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$dest = $dir . DIRECTORY_SEPARATOR . 'site-' . gmdate('Ymd-His') . '.sqlite';
if (!copy($src, $dest)) {
    fwrite(STDERR, "Falha ao copiar backup.\n");
    exit(1);
}

echo "Backup: {$dest}\n";
echo "Lembrete: mídia fica em assets/uploads/ (não entra no SQLite).\n";
