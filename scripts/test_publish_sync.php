<?php

declare(strict_types=1);

/**
 * Aceite: publicação CRM → Xhybrid published_sites.
 * Uso: php scripts/test_publish_sync.php
 */

$crmRoot = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'crm_software';
$siteRoot = dirname(__DIR__);

require_once $crmRoot . '/lib/db.php';
require_once $crmRoot . '/lib/crm/bootstrap.php';
require_once $siteRoot . '/lib/db.php';

$failed = 0;
$passed = 0;

function assert_true(bool $cond, string $label): void
{
    global $failed, $passed;
    if ($cond) {
        echo "[OK] {$label}\n";
        $passed++;
    } else {
        echo "[FAIL] {$label}\n";
        $failed++;
    }
}

$crm = db();
$site = \db(); // same function name — shadow: load site db differently

// Reabrir site.sqlite explicitamente
$sitePath = $siteRoot . '/data/site.sqlite';
$sitePdo = new PDO('sqlite:' . $sitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
require_once $siteRoot . '/lib/db.php';
// Trigger migrate via include already done — call migrate helper
$sitePdo->exec('SELECT 1');

// Force migrate published_sites
require_once $siteRoot . '/lib/db.php';
// db() from site was overwritten by crm db()! Need separate approach.

echo "Note: using explicit site PDO\n";

// Manually ensure table via CRM publisher ensure
assert_true(crm_xhybrid_configured(), 'xhybrid path configured');
assert_true(is_file(crm_xhybrid_db_path()), 'site.sqlite exists');

$agencyBrand = null;
$x = crm_xhybrid_pdo();
$stmt = $x->query("SELECT value FROM settings WHERE setting_key = 'brand_name' LIMIT 1");
$agencyBrand = $stmt ? (string) $stmt->fetchColumn() : '';
assert_true($agencyBrand !== '', 'agency brand_name present');

$id1 = crm_lead_save_manual($crm, [
    'company_name' => 'Eletricista Silva Aceite',
    'category' => 'eletricista',
    'phone' => '11911110001',
    'whatsapp' => '11911110001',
    'city' => 'São Paulo',
    'state' => 'SP',
    'neighborhood' => 'Moema',
    'has_website' => 0,
    'status' => 'NOVO',
]);
$id2 = crm_lead_save_manual($crm, [
    'company_name' => 'Eletricista Costa Aceite',
    'category' => 'eletricista',
    'phone' => '11911110002',
    'whatsapp' => '11911110002',
    'city' => 'Campinas',
    'state' => 'SP',
    'neighborhood' => 'Centro',
    'has_website' => 0,
    'status' => 'NOVO',
]);
assert_true($id1 > 0 && $id2 > 0, 'create 2 leads');

$r1 = crm_activate_site($crm, $id1);
$r2 = crm_activate_site($crm, $id2);
assert_true($r1['publish_ok'] === true, 'publish lead1: ' . ($r1['publish_warning'] ?: 'ok'));
assert_true($r2['publish_ok'] === true, 'publish lead2: ' . ($r2['publish_warning'] ?: 'ok'));

$l1 = crm_lead_get($crm, $id1);
$l2 = crm_lead_get($crm, $id2);
assert_true((string) $l1['slug'] !== '' && (string) $l1['url_code'] !== '', 'lead1 slug/code');
assert_true((string) $l2['slug'] !== '' && (string) $l2['url_code'] !== '', 'lead2 slug/code');
assert_true(crm_public_path($l1) !== crm_public_path($l2), 'distinct URLs');
assert_true(
    crm_appearance_combo_key([
        'look' => $l1['site_look'],
        'theme' => $l1['site_theme'],
        'font' => $l1['site_font'],
        'layout' => $l1['site_layout'],
        'media' => $l1['site_media'],
    ]) !== crm_appearance_combo_key([
        'look' => $l2['site_look'],
        'theme' => $l2['site_theme'],
        'font' => $l2['site_font'],
        'layout' => $l2['site_layout'],
        'media' => $l2['site_media'],
    ]),
    'distinct appearance'
);

$rows = $x->query('SELECT crm_lead_id, slug, url_code, site_active FROM published_sites WHERE crm_lead_id IN (' . (int) $id1 . ',' . (int) $id2 . ')')->fetchAll();
assert_true(count($rows) === 2, '2 published_sites rows');

$brandAfter = (string) $x->query("SELECT value FROM settings WHERE setting_key = 'brand_name' LIMIT 1")->fetchColumn();
assert_true($brandAfter === $agencyBrand, 'agency settings untouched');

$off = crm_deactivate_site($crm, $id1);
assert_true($off['publish_ok'] === true || $off['publish_warning'] === '', 'deactivate sync');
$active1 = (int) $x->query('SELECT site_active FROM published_sites WHERE crm_lead_id = ' . (int) $id1)->fetchColumn();
$active2 = (int) $x->query('SELECT site_active FROM published_sites WHERE crm_lead_id = ' . (int) $id2)->fetchColumn();
assert_true($active1 === 0, 'lead1 inactive in Xhybrid');
assert_true($active2 === 1, 'lead2 still active');

// Cleanup
crm_lead_delete($crm, $id1);
crm_lead_delete($crm, $id2);
$x->exec('DELETE FROM published_sites WHERE crm_lead_id IN (' . (int) $id1 . ',' . (int) $id2 . ')');

echo "\nPassed: {$passed}  Failed: {$failed}\n";
echo 'Agency brand still: ' . $brandAfter . "\n";
echo 'URLs were: ' . crm_public_path($l1) . ' / ' . crm_public_path($l2) . "\n";
exit($failed > 0 ? 1 : 0);
