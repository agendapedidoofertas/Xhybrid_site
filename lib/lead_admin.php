<?php

declare(strict_types=1);

require_once __DIR__ . '/published_sites.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/crm_bridge.php';

/**
 * Resolve lead_id from request (GET/POST).
 */
function lead_admin_request_id(): ?int
{
    $raw = $_POST['lead_id'] ?? $_GET['lead_id'] ?? null;
    if ($raw === null || $raw === '') {
        return null;
    }
    $id = (int) $raw;
    return $id > 0 ? $id : null;
}

/**
 * @return array<string, mixed>|null
 */
function lead_admin_resolve(?int $leadId): ?array
{
    if ($leadId === null || $leadId <= 0) {
        return null;
    }
    return published_site_get_by_lead(db(), $leadId);
}

/**
 * @param array<string, mixed> $row
 * @return array<string, string>
 */
function lead_admin_settings(array $row): array
{
    return published_site_settings_overlay($row);
}

function lead_admin_qs(int $leadId): string
{
    return 'lead_id=' . $leadId;
}

/**
 * Append lead_id to a relative admin URL.
 */
function lead_admin_href(string $path, ?int $leadId, array $extra = []): string
{
    $q = $extra;
    if ($leadId !== null && $leadId > 0) {
        $q['lead_id'] = $leadId;
    }
    if ($q === []) {
        return $path;
    }
    return $path . '?' . http_build_query($q);
}

/**
 * Keys that map to published_sites columns (not only payload).
 *
 * @return array<string, string> settings_key => column
 */
function lead_admin_column_map(): array
{
    return [
        'brand_name' => 'company_name',
        'whatsapp_number' => 'whatsapp',
        'email' => 'email',
        'maps_url' => 'maps_url',
        'instagram_url' => 'instagram_url',
        'facebook_url' => 'facebook_url',
        'appearance_look' => 'site_look',
        'appearance_theme' => 'site_theme',
        'appearance_font' => 'site_font',
        'appearance_layout' => 'site_layout',
        'appearance_media' => 'site_media',
        'site_plan' => 'plan_tier',
    ];
}

/**
 * Settings keys that must pass url_http_only.
 *
 * @return list<string>
 */
function lead_admin_url_keys(): array
{
    return [
        'maps_url', 'instagram_url', 'facebook_url', 'tiktok_url',
        'logo_url', 'website_url',
    ];
}

/**
 * Maps known settings keys into columns + payload_json, syncs CRM.
 *
 * @param array<string, mixed> $input
 * @return array<string, mixed> updated published_sites row
 */
function lead_admin_save_settings(PDO $pdo, int $leadId, array $input): array
{
    $colMap = lead_admin_column_map();
    $urlKeys = array_flip(lead_admin_url_keys());
    $fields = [];
    $payload = [];

    foreach ($input as $key => $value) {
        if (!is_string($key)) {
            continue;
        }
        $val = is_string($value) || is_numeric($value) ? (string) $value : '';
        if (isset($urlKeys[$key])) {
            $val = url_http_only($val);
        }

        if ($key === 'site_plan') {
            require_once __DIR__ . '/plans.php';
            $val = plan_normalize($val);
            $fields['plan_tier'] = $val;
            $payload['site_plan'] = $val;
            continue;
        }

        if (isset($colMap[$key])) {
            $col = $colMap[$key];
            if ($col === 'plan_tier') {
                require_once __DIR__ . '/plans.php';
                $val = plan_normalize($val);
            }
            $fields[$col] = $val;
        }

        // brand_city mirrors into city column when provided
        if ($key === 'brand_city' && $val !== '') {
            $fields['city'] = $val;
        }

        $payload[$key] = $val;
    }

    // Qualquer alteração de aparência pelo admin trava o CRM de sobrescrever
    $appearanceKeys = [
        'appearance_look', 'appearance_theme', 'appearance_font', 'appearance_layout',
        'appearance_media', 'appearance_combination_id', 'framework_skin', 'framework_bootswatch',
    ];
    foreach ($appearanceKeys as $ak) {
        if (array_key_exists($ak, $input)) {
            $payload['appearance_locked_by_admin'] = '1';
            break;
        }
    }

    if ($payload !== []) {
        $fields['payload'] = $payload;
    }

    $row = published_site_update_admin($pdo, $leadId, $fields);
    crm_bridge_push_published($row);
    return $row;
}

/**
 * Apply a niche preset to a lead (never agency settings table).
 */
function lead_admin_apply_preset(PDO $pdo, int $leadId, string $presetId): void
{
    require_once __DIR__ . '/presets.php';
    require_once __DIR__ . '/services.php';
    require_once __DIR__ . '/images.php';

    if ($presetId === 'xhybrid') {
        throw new InvalidArgumentException('Preset da agência não pode ser aplicado a um lead.');
    }

    $map = preset_function_map();
    if (!isset($map[$presetId])) {
        throw new InvalidArgumentException('Preset desconhecido.');
    }
    $fn = $map[$presetId];
    $preset = $fn();
    $settings = $preset['settings'] ?? [];
    $settings['site_preset'] = $presetId;

    $fields = [
        'site_preset' => $presetId,
        'payload' => $settings,
    ];
    if (!empty($settings['appearance_look'])) {
        $fields['site_look'] = (string) $settings['appearance_look'];
    }
    if (!empty($settings['appearance_theme'])) {
        $fields['site_theme'] = (string) $settings['appearance_theme'];
    }
    if (!empty($settings['appearance_font'])) {
        $fields['site_font'] = (string) $settings['appearance_font'];
    }
    if (!empty($settings['appearance_layout'])) {
        $fields['site_layout'] = (string) $settings['appearance_layout'];
    }
    if (!empty($settings['appearance_media'])) {
        $fields['site_media'] = (string) $settings['appearance_media'];
    }
    if (!empty($settings['brand_name'])) {
        $fields['company_name'] = (string) $settings['brand_name'];
    }

    $row = published_site_update_admin($pdo, $leadId, $fields);

    if (!empty($preset['services']) && is_array($preset['services'])) {
        services_replace_for_lead($pdo, $leadId, $preset['services']);
    }
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

    crm_bridge_push_published($row);
}

/** Set global context used by admin_layout eyebrow. */
function lead_admin_set_context(?int $leadId): void
{
    $GLOBALS['ADMIN_LEAD_ID'] = $leadId;
}

function lead_admin_context_id(): ?int
{
    $id = $GLOBALS['ADMIN_LEAD_ID'] ?? null;
    if ($id === null) {
        return null;
    }
    $n = (int) $id;
    return $n > 0 ? $n : null;
}
