<?php

declare(strict_types=1);

/**
 * Oferta pública (planos.html): preços, bullets e resumo dos Termos editáveis no admin.
 */

require_once __DIR__ . '/plans.php';

/**
 * @return array<string, mixed>
 */
function public_offer_defaults(): array
{
    return [
        'intro' => 'Site pronto, WhatsApp e manutenção no pacote. Escolha o plano e como pagar.',
        'eyebrow' => 'Oferta',
        'title' => 'Planos',
        'highlight' => 'pleno',
        'referral_note' => 'Indicação: R$ 50 por indicação aceita, até R$ 150. Preços da oferta vigente; impostos/taxas conforme a contratação.',
        'terms_summary' =>
            "Ao contratar, pagar ou usar os serviços Xhybrid / 8xd, você concorda com estes Termos e com a Política de Privacidade (LGPD).\n\n"
            . "Planos: Basic (subdomínio), Pleno (domínio próprio, personalização limitada) e Plus (personalização ampliada).\n\n"
            . "IA: revise dados comerciais antes de publicar.\n\n"
            . "Pagamento: Asaas e/ou Stripe. Inadimplência pode suspender o site.\n\n"
            . "Indicação: R$ 50 por indicação paga elegível, até R$ 150.",
        'terms_url' => '/termos.html',
        'privacy_summary' =>
            "A Xhybrid / 8xd trata dados para contratar e operar os planos Basic, Pleno e Plus (site, painel, CRM e cobrança).\n\n"
            . "Coletamos dados de checkout (empresa, e-mail, WhatsApp), plano, provedor (Asaas/Stripe) e conteúdo que você edita no painel.\n\n"
            . "Bases principais: execução de contrato e legítimo interesse operacional. Não vendemos listas.\n\n"
            . "Pagamentos são processados por Asaas e/ou Stripe. Dados ficam em servidor (pastas data/ não públicas).\n\n"
            . "Para acesso, correção ou exclusão (LGPD), fale pelos canais WhatsApp/e-mail da agência.",
        'privacy_full' => public_offer_privacy_full_default(),
        'privacy_url' => '/privacidade.php',
        'plans' => [
            'basic' => [
                'id' => 'basic',
                'label' => 'Basic',
                'planCents' => 49700,
                'maintenanceCents' => 5990,
                'bullets' => [
                    'Subdomínio *.8xd.com.br',
                    'Site completo com personalização mínima',
                    'Geração assistida por IA (você revisa)',
                    'WhatsApp + hospedagem/SSL no pacote',
                ],
            ],
            'pleno' => [
                'id' => 'pleno',
                'label' => 'Pleno',
                'planCents' => 69700,
                'maintenanceCents' => 7999,
                'bullets' => [
                    'Domínio próprio (taxa de registro à parte, se houver)',
                    'Personalização limitada',
                    'Preset de nicho e animações',
                    'Tudo do Basic + mais suporte',
                ],
            ],
            'plus' => [
                'id' => 'plus',
                'label' => 'Plus',
                'planCents' => 99700,
                'maintenanceCents' => 7999,
                'bullets' => [
                    'Domínio próprio',
                    'Personalização total · looks premium',
                    'Marca editável pelo cliente',
                    'FAQ, urgência e tetos amplos',
                ],
            ],
        ],
    ];
}

/**
 * Chaves de settings usadas pela oferta pública.
 *
 * @return list<string>
 */
function public_offer_setting_keys(): array
{
    $keys = [
        'offer_intro',
        'offer_eyebrow',
        'offer_title',
        'offer_highlight',
        'offer_referral_note',
        'offer_terms_summary',
        'offer_terms_url',
        'offer_privacy_summary',
        'offer_privacy_url',
    ];
    foreach (plan_ids() as $id) {
        $keys[] = "offer_{$id}_label";
        $keys[] = "offer_{$id}_plan_cents";
        $keys[] = "offer_{$id}_maint_cents";
        $keys[] = "offer_{$id}_bullets";
    }
    return $keys;
}

/**
 * Definições para settings_definitions (merge).
 *
 * @return array<string, array<string, mixed>>
 */
function public_offer_settings_definitions(): array
{
    $defs = [
        'offer_intro' => [
            'group' => 'offer', 'label' => 'Introdução da página Planos', 'max' => 320, 'type' => 'text',
            'default' => public_offer_defaults()['intro'],
        ],
        'offer_eyebrow' => [
            'group' => 'offer', 'label' => 'Eyebrow (ex.: Oferta)', 'max' => 40, 'type' => 'short',
            'default' => public_offer_defaults()['eyebrow'],
        ],
        'offer_title' => [
            'group' => 'offer', 'label' => 'Título (ex.: Planos)', 'max' => 60, 'type' => 'short',
            'default' => public_offer_defaults()['title'],
        ],
        'offer_highlight' => [
            'group' => 'offer', 'label' => 'Plano destacado', 'max' => 16, 'type' => 'choice',
            'choices' => ['basic', 'pleno', 'plus'],
            'default' => 'pleno',
        ],
        'offer_referral_note' => [
            'group' => 'offer', 'label' => 'Nota de indicação / rodapé', 'max' => 280, 'type' => 'text',
            'default' => public_offer_defaults()['referral_note'],
        ],
        'offer_terms_summary' => [
            'group' => 'offer', 'label' => 'Resumo dos Termos (modal)', 'max' => 6000, 'type' => 'text',
            'default' => public_offer_defaults()['terms_summary'],
        ],
        'offer_terms_url' => [
            'group' => 'offer', 'label' => 'URL da página completa dos Termos', 'max' => 160, 'type' => 'short',
            'default' => '/termos.html',
        ],
        'offer_privacy_summary' => [
            'group' => 'offer', 'label' => 'Resumo da Privacidade / LGPD (modal)', 'max' => 6000, 'type' => 'text',
            'default' => public_offer_defaults()['privacy_summary'],
        ],
        'offer_privacy_url' => [
            'group' => 'offer', 'label' => 'URL da página completa de Privacidade', 'max' => 160, 'type' => 'short',
            'default' => '/privacidade.html',
        ],
    ];

    $defaults = public_offer_defaults()['plans'];
    foreach (plan_ids() as $id) {
        $p = $defaults[$id];
        $defs["offer_{$id}_label"] = [
            'group' => 'offer', 'label' => "Rótulo {$id}", 'max' => 24, 'type' => 'short',
            'default' => (string) $p['label'],
        ];
        $defs["offer_{$id}_plan_cents"] = [
            'group' => 'offer', 'label' => "Preço plano {$id} (centavos)", 'max' => 10, 'type' => 'short',
            'default' => (string) $p['planCents'],
        ];
        $defs["offer_{$id}_maint_cents"] = [
            'group' => 'offer', 'label' => "Manutenção {$id} (centavos)", 'max' => 10, 'type' => 'short',
            'default' => (string) $p['maintenanceCents'],
        ];
        $defs["offer_{$id}_bullets"] = [
            'group' => 'offer', 'label' => "Bullets {$id} (1 por linha)", 'max' => 1200, 'type' => 'text',
            'default' => implode("\n", $p['bullets']),
        ];
    }

    return $defs;
}

/**
 * @return list<string>
 */
function public_offer_parse_bullets(string $raw): array
{
    $lines = preg_split('/\R+/', $raw) ?: [];
    $out = [];
    foreach ($lines as $line) {
        $line = trim((string) $line);
        if ($line === '') {
            continue;
        }
        $out[] = $line;
        if (count($out) >= 8) {
            break;
        }
    }
    return $out;
}

function public_offer_parse_cents(string $raw, int $fallback): int
{
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if ($digits === '') {
        return $fallback;
    }
    $n = (int) $digits;
    return $n > 0 ? $n : $fallback;
}

/**
 * @return array<string, mixed>
 */
function public_offer_get(PDO $pdo): array
{
    require_once __DIR__ . '/settings.php';
    $all = settings_all($pdo);
    $defaults = public_offer_defaults();

    $plans = [];
    foreach (plan_ids() as $id) {
        $d = $defaults['plans'][$id];
        $bullets = public_offer_parse_bullets((string) ($all["offer_{$id}_bullets"] ?? implode("\n", $d['bullets'])));
        if ($bullets === []) {
            $bullets = $d['bullets'];
        }
        $label = trim((string) ($all["offer_{$id}_label"] ?? $d['label']));
        if ($label === '') {
            $label = (string) $d['label'];
        }
        $plans[$id] = [
            'id' => $id,
            'label' => $label,
            'planCents' => public_offer_parse_cents((string) ($all["offer_{$id}_plan_cents"] ?? ''), (int) $d['planCents']),
            'maintenanceCents' => public_offer_parse_cents((string) ($all["offer_{$id}_maint_cents"] ?? ''), (int) $d['maintenanceCents']),
            'bullets' => $bullets,
        ];
    }

    $highlight = plan_normalize((string) ($all['offer_highlight'] ?? 'pleno'));
    $termsUrl = trim((string) ($all['offer_terms_url'] ?? '/termos.html'));
    if ($termsUrl === '') {
        $termsUrl = '/termos.html';
    }
    if (!preg_match('#^https?://#i', $termsUrl) && !str_starts_with($termsUrl, '/')) {
        $termsUrl = '/' . ltrim($termsUrl, '/');
    }
    $privacyUrl = trim((string) ($all['offer_privacy_url'] ?? '/privacidade.html'));
    if ($privacyUrl === '') {
        $privacyUrl = '/privacidade.html';
    }
    if (!preg_match('#^https?://#i', $privacyUrl) && !str_starts_with($privacyUrl, '/')) {
        $privacyUrl = '/' . ltrim($privacyUrl, '/');
    }

    $intro = trim((string) ($all['offer_intro'] ?? $defaults['intro']));
    if ($intro === '') {
        $intro = $defaults['intro'];
    }
    $eyebrow = trim((string) ($all['offer_eyebrow'] ?? $defaults['eyebrow']));
    if ($eyebrow === '') {
        $eyebrow = $defaults['eyebrow'];
    }
    $title = trim((string) ($all['offer_title'] ?? $defaults['title']));
    if ($title === '') {
        $title = $defaults['title'];
    }
    $referral = trim((string) ($all['offer_referral_note'] ?? $defaults['referral_note']));
    if ($referral === '') {
        $referral = $defaults['referral_note'];
    }
    $summary = trim((string) ($all['offer_terms_summary'] ?? $defaults['terms_summary']));
    if ($summary === '') {
        $summary = $defaults['terms_summary'];
    }
    $privacySummary = trim((string) ($all['offer_privacy_summary'] ?? $defaults['privacy_summary']));
    if ($privacySummary === '') {
        $privacySummary = $defaults['privacy_summary'];
    }

    return [
        'intro' => $intro,
        'eyebrow' => $eyebrow,
        'title' => $title,
        'highlight' => $highlight,
        'referral_note' => $referral,
        'terms_summary' => $summary,
        'terms_url' => $termsUrl,
        'privacy_summary' => $privacySummary,
        'privacy_url' => $privacyUrl,
        'plans' => $plans,
    ];
}

/**
 * Salva oferta a partir de POST (valores já crus).
 *
 * @param array<string, string> $input
 */
function public_offer_save(PDO $pdo, array $input): void
{
    require_once __DIR__ . '/settings.php';

    $payload = [];
    foreach (public_offer_setting_keys() as $key) {
        if (array_key_exists($key, $input)) {
            $payload[$key] = (string) $input[$key];
        }
    }

    // Paths relativos locais
    foreach (['offer_terms_url', 'offer_privacy_url'] as $urlKey) {
        if (isset($payload[$urlKey])) {
            $u = trim($payload[$urlKey]);
            if ($u !== '' && !preg_match('#^https?://#i', $u) && !str_starts_with($u, '/')) {
                $payload[$urlKey] = '/' . ltrim($u, '/');
            }
        }
    }

    settings_save_many($pdo, $payload);
}

/**
 * Converte reais "697,00" ou "697" → centavos string.
 */
function public_offer_brl_to_cents_string(string $raw): string
{
    $raw = trim(str_replace(['R$', ' '], '', $raw));
    if ($raw === '') {
        return '';
    }
    if (str_contains($raw, ',') || str_contains($raw, '.')) {
        $normalized = str_replace('.', '', $raw);
        $normalized = str_replace(',', '.', $normalized);
        $float = (float) $normalized;
        return (string) (int) round($float * 100);
    }
    if (ctype_digit($raw)) {
        // Se número pequeno, assume reais inteiros; se >= 1000 pode já ser centavos — heurística:
        // admin form pedirá reais; valores tipicamente 59–999 → reais
        $n = (int) $raw;
        if ($n < 1000) {
            return (string) ($n * 100);
        }
        return (string) $n;
    }
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    return $digits;
}
