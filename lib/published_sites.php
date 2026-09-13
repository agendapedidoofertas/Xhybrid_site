<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>|null
 */
function published_site_find(PDO $pdo, string $slug, string $urlCode): ?array
{
    $stmt = $pdo->prepare(
        'SELECT * FROM published_sites WHERE slug = :s AND url_code = :c LIMIT 1'
    );
    $stmt->execute([':s' => $slug, ':c' => $urlCode]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Lookup público: slug + crm_lead_id + letra (url_code).
 *
 * @return array<string, mixed>|null
 */
function published_site_find_public(PDO $pdo, string $slug, int $crmLeadId, string $letter): ?array
{
    if ($crmLeadId <= 0 || !preg_match('/^[a-z]$/', $letter)) {
        return null;
    }
    $stmt = $pdo->prepare(
        'SELECT * FROM published_sites
         WHERE crm_lead_id = :id AND slug = :s AND url_code = :c
         LIMIT 1'
    );
    $stmt->execute([
        ':id' => $crmLeadId,
        ':s' => $slug,
        ':c' => $letter,
    ]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/**
 * Torna href/src de assets relativos em absolutos a partir da raiz.
 * Não reescreve links .html (páginas do site) — isso fica no JS do lead.
 */
function published_site_absolutize_html(string $html): string
{
    if (!str_contains($html, '<base ')) {
        $html = preg_replace(
            '/<head([^>]*)>/i',
            '<head$1><base href="/">',
            $html,
            1
        ) ?? $html;
    }

    $html = preg_replace_callback(
        // Só atributos href/src reais (não data-site-href, data-img-src, etc.)
        '~(?<=\s)(href|src)=([\'"])(?!https?:|//|#|data:|mailto:|tel:|/)([^\'"]+)\2~i',
        static function (array $m): string {
            $path = ltrim(str_replace('\\', '/', $m[3]), './');
            // Páginas HTML do site: manter relativo; o JS do lead prefixa o path público
            if (preg_match('/\.html(?:[?#].*)?$/i', $path)) {
                return $m[0];
            }
            return $m[1] . '=' . $m[2] . '/' . $path . $m[2];
        },
        $html
    ) ?? $html;

    return $html;
}

/**
 * Overlay de settings para um lead publicado (preset em memória + payload).
 *
 * @param array<string, mixed> $row
 * @return array<string, string>
 */
function published_site_settings_overlay(array $row): array
{
    require_once __DIR__ . '/presets.php';
    require_once __DIR__ . '/hours.php';

    $preset = strtolower(trim((string) ($row['site_preset'] ?? 'eletricista')));
    if ($preset === '') {
        $preset = 'eletricista';
    }
    $fn = 'preset_' . preg_replace('/[^a-z0-9_]/', '', $preset);
    $base = [];
    if (is_string($fn) && function_exists($fn)) {
        $pack = $fn();
        $base = $pack['settings'] ?? [];
    } elseif (function_exists('preset_eletricista')) {
        $pack = preset_eletricista();
        $base = $pack['settings'] ?? [];
    }

    $payload = [];
    $raw = trim((string) ($row['payload_json'] ?? ''));
    if ($raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            foreach ($decoded as $k => $v) {
                if (is_string($k) && (is_string($v) || is_numeric($v))) {
                    $payload[$k] = (string) $v;
                }
            }
        }
    }

    $contact = [
        'brand_name' => (string) ($row['company_name'] ?? ''),
        'brand_city' => (string) ($row['city'] ?? ''),
        'whatsapp_number' => (string) (($row['whatsapp'] ?? '') !== '' ? $row['whatsapp'] : ($row['phone'] ?? '')),
        'email' => (string) ($row['email'] ?? ''),
        'address' => trim(implode(', ', array_filter([
            trim((string) ($row['address_street'] ?? '')),
            trim((string) ($row['address_number'] ?? '')),
            trim((string) ($row['neighborhood'] ?? '')),
            trim((string) ($row['city'] ?? '')),
            trim((string) ($row['state'] ?? '')),
        ]))),
        'maps_url' => (string) ($row['maps_url'] ?? ''),
        'instagram_url' => (string) ($row['instagram_url'] ?? ''),
        'facebook_url' => (string) ($row['facebook_url'] ?? ''),
        'appearance_look' => (string) ($row['site_look'] ?? ''),
        'appearance_theme' => (string) ($row['site_theme'] ?? ''),
        'appearance_font' => (string) ($row['site_font'] ?? ''),
        'appearance_layout' => (string) ($row['site_layout'] ?? ''),
        'appearance_media' => (string) ($row['site_media'] ?? ''),
        'site_plan' => (string) (($row['plan_tier'] ?? '') !== '' ? $row['plan_tier'] : 'basic'),
    ];

    $city = trim((string) ($row['city'] ?? ''));
    $name = trim((string) ($row['company_name'] ?? ''));
    if ($name !== '') {
        $contact['brand_seo_title'] = $city !== ''
            ? $name . ' — ' . $city
            : $name;
        $contact['brand_seo_description'] = 'Site de ' . $name . ($city !== '' ? ' em ' . $city : '') . '. Contato pelo WhatsApp.';
        $contact['whatsapp_message'] = 'Olá! Vim pelo site de ' . $name . '.';
        // Wordmark: só preenche se o payload ainda não tiver override manual
        $hasShort = trim((string) ($payload['brand_short'] ?? '')) !== '';
        if (!$hasShort) {
            require_once __DIR__ . '/brand_mark.php';
            $mark = brand_mark_split($name);
            if ($mark['short'] !== '') {
                $contact['brand_short'] = $mark['short'];
            }
            if ($mark['tag'] !== '') {
                $contact['brand_tag'] = $mark['tag'];
            }
        }
    }

    // Formulário de contato → WhatsApp do lead (não SMTP/e-mail)
    $contact['contact_form_intro'] = 'Preencha e a mensagem abre no WhatsApp.';
    $contact['contact_form_btn'] = 'Enviar no WhatsApp';

    $waRaw = (string) ($contact['whatsapp_number'] ?? '');
    $waDigits = preg_replace('/\D+/', '', $waRaw) ?? '';
    if ($waDigits !== '') {
        if (strlen($waDigits) >= 10 && strlen($waDigits) <= 11 && !str_starts_with($waDigits, '55')) {
            $waDigits = '55' . $waDigits;
        }
        $contact['whatsapp_number'] = $waDigits;
    }

    $merged = array_merge($base, $payload, array_filter($contact, static fn ($v) => $v !== ''));

    // Horário do Maps → pt-BR (substitui linhas do preset / payload em inglês)
    $hoursRaw = trim((string) ($row['opening_hours'] ?? ''));
    for ($i = 1; $i <= 7; $i++) {
        $merged['footer_hours_line' . $i] = '';
    }
    if ($hoursRaw !== '') {
        $merged['footer_hours_title'] = 'Atendimento';
        foreach (hours_format_pt($hoursRaw) as $idx => $line) {
            if ($idx >= 7) {
                break;
            }
            $merged['footer_hours_line' . ($idx + 1)] = $line;
        }
    }

    $out = [];
    foreach ($merged as $k => $v) {
        if (is_string($k)) {
            $out[$k] = (string) $v;
        }
    }
    return $out;
}

/**
 * @return list<array<string, mixed>>
 */
function published_site_list(PDO $pdo, ?int $activeOnly = null, ?string $planTier = null): array
{
    $sql = 'SELECT * FROM published_sites';
    $parts = [];
    $params = [];
    if ($activeOnly !== null) {
        $parts[] = 'site_active = :a';
        $params[':a'] = $activeOnly;
    }
    if ($planTier !== null && $planTier !== '') {
        $tier = strtolower(trim($planTier));
        if (in_array($tier, ['basic', 'medium', 'pro'], true)) {
            $parts[] = 'plan_tier = :plan_tier';
            $params[':plan_tier'] = $tier;
        }
    }
    if ($parts !== []) {
        $sql .= ' WHERE ' . implode(' AND ', $parts);
    }
    $sql .= ' ORDER BY updated_at DESC, company_name ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll() ?: [];
}

/**
 * @return array<string, mixed>|null
 */
function published_site_get_by_lead(PDO $pdo, int $crmLeadId): ?array
{
    if ($crmLeadId <= 0) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM published_sites WHERE crm_lead_id = :id LIMIT 1');
    $stmt->execute([':id' => $crmLeadId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function published_site_public_path(array $row): string
{
    $slug = trim((string) ($row['slug'] ?? ''));
    $id = (int) ($row['crm_lead_id'] ?? 0);
    $code = trim((string) ($row['url_code'] ?? ''));
    if ($slug === '' || $id <= 0 || $code === '') {
        return '';
    }
    return '/' . $slug . '/' . $id . $code;
}

/**
 * @return array<string, string>
 */
function published_site_decode_payload(array $row): array
{
    $out = [];
    $raw = trim((string) ($row['payload_json'] ?? ''));
    if ($raw === '') {
        return $out;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $out;
    }
    foreach ($decoded as $k => $v) {
        if (is_string($k) && (is_string($v) || is_numeric($v))) {
            $out[$k] = (string) $v;
        }
    }
    return $out;
}

/**
 * Atualiza site do lead a partir do admin Xhybrid.
 *
 * @param array<string, mixed> $fields Colunas + opcional payload (array|string)
 * @return array<string, mixed> Row atualizada
 */
function published_site_update_admin(PDO $pdo, int $crmLeadId, array $fields): array
{
    $row = published_site_get_by_lead($pdo, $crmLeadId);
    if (!$row) {
        throw new RuntimeException('Site do lead #' . $crmLeadId . ' não encontrado');
    }

    $cols = [
        'company_name', 'phone', 'whatsapp', 'email',
        'address_street', 'address_number', 'address_complement',
        'neighborhood', 'city', 'state', 'postal_code',
        'maps_url', 'website_url', 'instagram_url', 'facebook_url',
        'opening_hours', 'site_preset', 'plan_tier',
        'site_look', 'site_theme', 'site_font', 'site_layout', 'site_media',
    ];

    $sets = ['updated_at = :updated_at'];
    $params = [
        ':updated_at' => gmdate('c'),
        ':id' => $crmLeadId,
    ];

    foreach ($cols as $col) {
        if (!array_key_exists($col, $fields)) {
            continue;
        }
        $sets[] = $col . ' = :' . $col;
        $params[':' . $col] = (string) $fields[$col];
    }

    $payload = published_site_decode_payload($row);
    if (isset($fields['payload']) && is_array($fields['payload'])) {
        foreach ($fields['payload'] as $k => $v) {
            if (!is_string($k)) {
                continue;
            }
            $payload[$k] = is_string($v) || is_numeric($v) ? (string) $v : '';
        }
    }

    // Espelha contato/aparência no payload usado pelo overlay
    if (isset($fields['company_name'])) {
        $payload['brand_name'] = (string) $fields['company_name'];
        require_once __DIR__ . '/brand_mark.php';
        $mark = brand_mark_split((string) $fields['company_name']);
        // Só auto-preenche se o admin não enviou brand_short no payload
        $manualShort = isset($fields['payload']['brand_short'])
            && trim((string) $fields['payload']['brand_short']) !== '';
        if (!$manualShort) {
            $payload['brand_short'] = $mark['short'];
            $payload['brand_tag'] = $mark['tag'];
        }
    }
    if (isset($fields['city'])) {
        $payload['brand_city'] = (string) $fields['city'];
    }
    if (isset($fields['whatsapp']) || isset($fields['phone'])) {
        $wa = (string) ($fields['whatsapp'] ?? $row['whatsapp'] ?? '');
        if ($wa === '') {
            $wa = (string) ($fields['phone'] ?? $row['phone'] ?? '');
        }
        $payload['whatsapp_number'] = $wa;
    }
    if (isset($fields['email'])) {
        $payload['email'] = (string) $fields['email'];
    }
    if (isset($fields['instagram_url'])) {
        $payload['instagram_url'] = (string) $fields['instagram_url'];
    }
    if (isset($fields['facebook_url'])) {
        $payload['facebook_url'] = (string) $fields['facebook_url'];
    }
    if (isset($fields['maps_url'])) {
        $payload['maps_url'] = (string) $fields['maps_url'];
    }
    foreach (['site_look' => 'appearance_look', 'site_theme' => 'appearance_theme', 'site_font' => 'appearance_font', 'site_layout' => 'appearance_layout', 'site_media' => 'appearance_media'] as $col => $pkey) {
        if (isset($fields[$col])) {
            $payload[$pkey] = (string) $fields[$col];
        }
    }

    $street = (string) ($fields['address_street'] ?? $row['address_street'] ?? '');
    $number = (string) ($fields['address_number'] ?? $row['address_number'] ?? '');
    $neigh = (string) ($fields['neighborhood'] ?? $row['neighborhood'] ?? '');
    $city = (string) ($fields['city'] ?? $row['city'] ?? '');
    $state = (string) ($fields['state'] ?? $row['state'] ?? '');
    $payload['address'] = trim(implode(', ', array_filter([$street, $number, $neigh, $city, $state])));

    $sets[] = 'payload_json = :payload_json';
    $params[':payload_json'] = json_encode($payload, JSON_UNESCAPED_UNICODE) ?: '{}';

    $pdo->prepare(
        'UPDATE published_sites SET ' . implode(', ', $sets) . ' WHERE crm_lead_id = :id'
    )->execute($params);

    $updated = published_site_get_by_lead($pdo, $crmLeadId);
    if (!$updated) {
        throw new RuntimeException('Falha ao releitura do site do lead');
    }
    return $updated;
}
