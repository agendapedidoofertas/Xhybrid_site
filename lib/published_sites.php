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
        '~\b(href|src)=([\'"])(?!https?:|//|#|data:|mailto:|tel:|/)([^\'"]+)\2~i',
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
    ];

    $city = trim((string) ($row['city'] ?? ''));
    $name = trim((string) ($row['company_name'] ?? ''));
    if ($name !== '') {
        $contact['brand_seo_title'] = $city !== ''
            ? $name . ' — ' . $city
            : $name;
        $contact['brand_seo_description'] = 'Site de ' . $name . ($city !== '' ? ' em ' . $city : '') . '. Contato pelo WhatsApp.';
        $contact['whatsapp_message'] = 'Olá! Vim pelo site de ' . $name . '.';
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
