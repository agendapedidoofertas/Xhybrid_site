<?php

declare(strict_types=1);

/**
 * Script de Backup Automático Diário (HostGator / cPanel / CLI)
 *
 * Uso no cPanel Cron Jobs:
 * /usr/local/bin/php /home/SEU_USUARIO/public_html/cron/backup.php
 *
 * Ou via HTTP com chave secreta:
 * https://seudominio.com.br/cron/backup.php?key=SUA_CHAVE_AQUI
 */

$isCli = (PHP_SAPI === 'cli');

// Se chamado via navegador, exige autenticação de sessão admin ou chave secreta
if (!$isCli) {
    require_once dirname(__DIR__) . '/lib/auth.php';
    auth_boot_session();
    $admin = current_user();

    $cronKey = getenv('CRON_BACKUP_KEY') ?: '';
    $keyFile = dirname(__DIR__) . '/data/backup_key.php';
    if ($cronKey === '' && is_file($keyFile)) {
        $loaded = include $keyFile;
        if (is_string($loaded)) {
            $cronKey = trim($loaded);
        }
    }

    $providedKey = (string) ($_GET['key'] ?? '');
    $authorized = (user_is_admin($admin) || ($cronKey !== '' && hash_equals($cronKey, $providedKey)));

    if (!$authorized) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Acesso negado.']);
        exit;
    }
}

$baseDir = dirname(__DIR__);
$backupDir = $baseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'backups';

if (!is_dir($backupDir)) {
    @mkdir($backupDir, 0750, true);
}

// 1. Garante que .htaccess proteja a pasta contra download público HTTP
$htaccessFile = $backupDir . DIRECTORY_SEPARATOR . '.htaccess';
if (!is_file($htaccessFile)) {
    @file_put_contents($htaccessFile, "# Bloqueio de acesso web direto\n<IfModule !mod_authz_core.c>\n    Order deny,allow\n    Deny from all\n</IfModule>\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n");
}

$siteDb = $baseDir . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'site.sqlite';
$crmDb = dirname($baseDir) . DIRECTORY_SEPARATOR . 'crm_software' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'crm.sqlite';

$timestamp = date('Y-m-d-His');
$zipPath = $backupDir . DIRECTORY_SEPARATOR . 'backup-' . $timestamp . '.zip';

$filesToBackup = [];
if (is_file($siteDb)) {
    $filesToBackup['site.sqlite'] = $siteDb;
}
if (is_file($crmDb)) {
    $filesToBackup['crm.sqlite'] = $crmDb;
}

if (empty($filesToBackup)) {
    $msg = 'Nenhum banco de dados encontrado para backup.';
    if ($isCli) {
        echo "[ERROR] {$msg}\n";
    } else {
        echo json_encode(['ok' => false, 'error' => $msg]);
    }
    exit(1);
}

$createdZip = false;
$savedFiles = [];

// Tenta compactar em .zip se ZipArchive estiver habilitado (padrão cPanel/HostGator)
if (class_exists('ZipArchive')) {
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($filesToBackup as $localName => $fullPath) {
            $tempCopy = $backupDir . DIRECTORY_SEPARATOR . 'temp_' . $localName;
            if (@copy($fullPath, $tempCopy)) {
                $zip->addFile($tempCopy, $localName);
            } else {
                $zip->addFile($fullPath, $localName);
            }
        }
        $zip->close();
        $createdZip = true;
        $savedFiles[] = basename($zipPath);

        foreach ($filesToBackup as $localName => $_) {
            $tempCopy = $backupDir . DIRECTORY_SEPARATOR . 'temp_' . $localName;
            if (is_file($tempCopy)) {
                @unlink($tempCopy);
            }
        }
    }
}

// Fallback caso ZipArchive não esteja ativo: cópia atômica dos arquivos .sqlite
if (!$createdZip) {
    foreach ($filesToBackup as $localName => $fullPath) {
        $destFile = $backupDir . DIRECTORY_SEPARATOR . 'backup-' . $timestamp . '-' . $localName;
        if (@copy($fullPath, $destFile)) {
            $savedFiles[] = basename($destFile);
        }
    }
}

// 2. Rotação automática de backups: exclui arquivos com mais de 14 dias
$now = time();
$maxAge = 14 * 86400; // 14 dias em segundos
$cleaned = 0;

$entries = scandir($backupDir) ?: [];
foreach ($entries as $file) {
    if (str_starts_with($file, 'backup-') && (str_ends_with($file, '.zip') || str_ends_with($file, '.sqlite'))) {
        $fullPath = $backupDir . DIRECTORY_SEPARATOR . $file;
        if (($now - filemtime($fullPath)) > $maxAge) {
            if (@unlink($fullPath)) {
                $cleaned++;
            }
        }
    }
}

$totalBytes = 0;
foreach ($savedFiles as $sf) {
    $p = $backupDir . DIRECTORY_SEPARATOR . $sf;
    if (is_file($p)) {
        $totalBytes += filesize($p);
    }
}

$result = [
    'ok' => true,
    'zip_created' => $createdZip,
    'files' => $savedFiles,
    'total_bytes' => $totalBytes,
    'old_backups_purged' => $cleaned,
    'timestamp' => $timestamp,
];

if ($isCli) {
    echo "[SUCCESS] Backup gerado com sucesso (" . count($savedFiles) . " arquivo(s), " . round($totalBytes / 1024, 1) . " KB). Backups antigos removidos: {$cleaned}\n";
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
