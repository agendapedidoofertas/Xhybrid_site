<?php

declare(strict_types=1);

/**
 * Reaplica imagens (e look/layout vazios) de todos os published_sites
 * sem sobrescrever nome/textos do lead.
 *
 * Uso: php scripts/refresh_all_lead_presets.php
 */

require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/presets.php';
require_once dirname(__DIR__) . '/lib/images.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';

$pdo = db();
$rows = $pdo->query(
    'SELECT id, crm_lead_id, slug, url_code, site_preset, site_look, site_layout, site_theme, site_font, site_media, site_active, company_name
     FROM published_sites
     WHERE crm_lead_id IS NOT NULL AND crm_lead_id > 0
     ORDER BY id'
)->fetchAll(PDO::FETCH_ASSOC) ?: [];

$map = preset_function_map();
$ok = 0;
$skip = 0;
$fail = 0;

foreach ($rows as $row) {
    $leadId = (int) $row['crm_lead_id'];
    $presetId = strtolower(trim((string) ($row['site_preset'] ?? '')));
    if ($presetId === '' || $presetId === 'xhybrid' || !isset($map[$presetId])) {
        $presetId = 'eletricista';
    }

    try {
        $fn = $map[$presetId];
        $preset = $fn();
        $settings = $preset['settings'] ?? [];

        if (!empty($preset['images']) && is_array($preset['images'])) {
            images_upsert_by_slug_for_lead($pdo, $leadId, $preset['images']);
            $keep = [];
            foreach ($preset['images'] as $img) {
                if (!empty($img['slug'])) {
                    $keep[] = (string) $img['slug'];
                }
            }
            images_deactivate_unlisted_for_lead($pdo, $leadId, $keep);
        }
        images_deactivate_unavailable($pdo, $leadId);

        $fields = ['site_preset' => $presetId];
        // Só preenche aparência se estiver vazia (não apaga customização)
        if (trim((string) ($row['site_look'] ?? '')) === '' && !empty($settings['appearance_look'])) {
            $fields['site_look'] = (string) $settings['appearance_look'];
        }
        if (trim((string) ($row['site_theme'] ?? '')) === '' && !empty($settings['appearance_theme'])) {
            $fields['site_theme'] = (string) $settings['appearance_theme'];
        }
        if (trim((string) ($row['site_font'] ?? '')) === '' && !empty($settings['appearance_font'])) {
            $fields['site_font'] = (string) $settings['appearance_font'];
        }
        if (trim((string) ($row['site_layout'] ?? '')) === '' && !empty($settings['appearance_layout'])) {
            $fields['site_layout'] = (string) $settings['appearance_layout'];
        }
        if (trim((string) ($row['site_media'] ?? '')) === '' && !empty($settings['appearance_media'])) {
            $fields['site_media'] = (string) $settings['appearance_media'];
        }

        // Layouts que quebravam cards: força soft nos ativos elétricos se ainda for bento/magazine extremos — só se look causar escada conhecido
        $layout = strtolower(trim((string) ($row['site_layout'] ?? ($fields['site_layout'] ?? ''))));
        if (in_array($layout, ['bento', 'magazine'], true) && $presetId === 'eletricista') {
            $fields['site_layout'] = 'soft';
        }

        published_site_update_admin($pdo, $leadId, $fields);
        $ok++;
        echo sprintf(
            "[OK] lead=%d %s/%s%s preset=%s\n",
            $leadId,
            $row['slug'],
            $row['url_code'],
            $leadId,
            $presetId
        );
    } catch (Throwable $e) {
        $fail++;
        echo sprintf("[FAIL] lead=%d — %s\n", $leadId, $e->getMessage());
    }
}

echo sprintf("\nResumo: OK=%d FAIL=%d SKIP=%d total=%d\n", $ok, $fail, $skip, count($rows));
