<?php

declare(strict_types=1);

/**
 * Ponte Xhybrid → CRM (PDO direto, sem carregar db() do CRM).
 */

function crm_bridge_root(): string
{
    $env = getenv('CRM_SOFTWARE_ROOT');
    if (is_string($env) && trim($env) !== '' && is_dir(trim($env))) {
        return rtrim(trim($env), '\\/');
    }
    $cfg = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'crm_bridge.php';
    if (is_file($cfg)) {
        $path = include $cfg;
        if (is_string($path) && trim($path) !== '' && is_dir(trim($path))) {
            return rtrim(trim($path), '\\/');
        }
    }
    $sibling = dirname(__DIR__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'crm_software';
    $real = realpath($sibling);
    return $real !== false ? $real : $sibling;
}

function crm_bridge_sqlite_path(): string
{
    $path = crm_bridge_root() . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'crm.sqlite';
    $real = realpath($path);
    if ($real === false) {
        return $path;
    }
    $root = realpath(crm_bridge_root());
    if ($root === false || !str_starts_with($real, $root)) {
        throw new RuntimeException('Caminho do CRM sqlite fora do diretório esperado.');
    }
    return $real;
}

function crm_bridge_configured(): bool
{
    return is_file(crm_bridge_sqlite_path());
}

function crm_bridge_pdo(): PDO
{
    $path = crm_bridge_sqlite_path();
    if (!is_file($path)) {
        throw new RuntimeException('CRM sqlite não encontrado em ' . $path);
    }
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    return $pdo;
}

/**
 * Empurra row de published_sites para o lead no CRM.
 * @param array<string, mixed> $publishedRow
 * @return string Aviso vazio se ok
 */
function crm_bridge_push_published(array $publishedRow): string
{
    if (!crm_bridge_configured()) {
        return 'Site salvo no Xhybrid; CRM não configurado (pasta irmã crm_software ou data/crm_bridge.php).';
    }
    $helper = crm_bridge_root() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'crm' . DIRECTORY_SEPARATOR . 'lead_from_published.php';
    if (!is_file($helper)) {
        return 'Site salvo; arquivo lead_from_published.php não encontrado no CRM.';
    }
    require_once $helper;
    try {
        crm_lead_update_from_published(crm_bridge_pdo(), $publishedRow);
        return '';
    } catch (Throwable $e) {
        return 'Site salvo no Xhybrid; sync CRM falhou: ' . $e->getMessage();
    }
}
