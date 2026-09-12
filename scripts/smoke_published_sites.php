<?php

declare(strict_types=1);

/** Smoke: migrate published_sites + agency settings intact. */
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';

$pdo = db();
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
echo 'tables: ' . implode(',', $tables) . "\n";
echo 'has published_sites: ' . (in_array('published_sites', $tables, true) ? 'yes' : 'no') . "\n";
$brand = (string) $pdo->query("SELECT value FROM settings WHERE setting_key = 'brand_name' LIMIT 1")->fetchColumn();
echo 'brand_name: ' . $brand . "\n";
$count = (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
echo 'settings_count: ' . $count . "\n";
exit(in_array('published_sites', $tables, true) && $brand !== '' ? 0 : 1);
