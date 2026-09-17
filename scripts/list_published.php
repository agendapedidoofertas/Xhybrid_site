<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/db.php';

$pdo = db();
$rows = $pdo->query(
    'SELECT id, crm_lead_id, slug, url_code, site_preset, site_look, site_layout, site_active, company_name
     FROM published_sites ORDER BY id'
)->fetchAll(PDO::FETCH_ASSOC);

echo 'published_sites: ' . count($rows) . PHP_EOL;
foreach ($rows as $r) {
    echo sprintf(
        "#%d lead=%s %s/%s%s preset=%s look=%s layout=%s active=%s name=%s\n",
        (int) $r['id'],
        (string) $r['crm_lead_id'],
        (string) $r['slug'],
        (string) $r['url_code'],
        (string) $r['crm_lead_id'],
        (string) $r['site_preset'],
        (string) $r['site_look'],
        (string) $r['site_layout'],
        (string) $r['site_active'],
        (string) $r['company_name']
    );
}
