<?php

declare(strict_types=1);

/**
 * Planos comerciais: basic | pleno | plus
 * Aliases legado: medium→pleno, pro→plus, essencial/profissional/personalizado.
 */
function plan_ids(): array
{
    return ['basic', 'pleno', 'plus'];
}

/**
 * @return array<string, string>
 */
function plan_aliases(): array
{
    return [
        'essencial' => 'basic',
        'profissional' => 'pleno',
        'personalizado' => 'plus',
        'basic' => 'basic',
        'medium' => 'pleno',
        'pleno' => 'pleno',
        'pro' => 'plus',
        'plus' => 'plus',
    ];
}

function plan_normalize(string $plan): string
{
    $plan = strtolower(trim($plan));
    $aliases = plan_aliases();
    if (isset($aliases[$plan])) {
        return $aliases[$plan];
    }
    return in_array($plan, plan_ids(), true) ? $plan : 'basic';
}

/** Edição de Marca — default: só Plus. */
function plan_allows_brand_edit(string $plan): bool
{
    $plan = plan_normalize($plan);
    if (!function_exists('db') || !function_exists('permissions_plan_allows')) {
        if (is_file(__DIR__ . '/permissions.php')) {
            require_once __DIR__ . '/permissions.php';
        }
        if (is_file(__DIR__ . '/db.php')) {
            require_once __DIR__ . '/db.php';
        }
    }
    try {
        if (function_exists('db') && function_exists('permissions_plan_allows')) {
            return permissions_plan_allows(db(), $plan, 'brand_edit');
        }
    } catch (Throwable $e) {
        // fallback
    }
    return $plan === 'plus';
}

function plan_labels(): array
{
    return [
        'basic' => 'Basic',
        'pleno' => 'Pleno',
        'plus' => 'Plus',
        // legado (labels se algum código ainda passar medium/pro cru)
        'medium' => 'Pleno',
        'pro' => 'Plus',
    ];
}

/**
 * @return array<string, string>
 */
function plan_blurbs(): array
{
    return [
        'basic' => 'Subdomínio *.8xd.com.br · site completo · personalização mínima · manutenção R$ 59,90',
        'pleno' => 'Domínio próprio · personalização limitada · manutenção R$ 79,99',
        'plus' => 'Domínio próprio · personalização total · Marca e looks premium · manutenção R$ 79,99',
    ];
}

/**
 * @return array<string, string>
 */
function plan_bundle(string $plan): array
{
    $plan = plan_normalize($plan);

    if ($plan === 'basic') {
        return [
            'site_plan' => 'basic',
            'feature_page_sobre' => '1',
            'feature_page_galeria' => '1',
            'feature_page_contato' => '1',
            'feature_animations' => '0',
            'feature_looks_premium' => '0',
            'feature_preset_nicho' => '0',
            'limit_services' => '3',
            'limit_gallery' => '6',
            'section_hero' => '1',
            'section_features' => '1',
            'section_works' => '0',
            'section_area' => '0',
            'section_testimonials' => '0',
            'section_faq' => '0',
            'section_cta' => '1',
            'urgency_enabled' => '0',
            'appearance_look' => 'tech-glass',
            'appearance_theme' => 'preto',
            'appearance_font' => 'tech',
            'appearance_layout' => 'soft',
            'appearance_media' => 'classic',
        ];
    }

    if ($plan === 'pleno') {
        return [
            'site_plan' => 'pleno',
            'feature_page_sobre' => '1',
            'feature_page_galeria' => '1',
            'feature_page_contato' => '1',
            'feature_animations' => '1',
            'feature_looks_premium' => '0',
            'feature_preset_nicho' => '1',
            'limit_services' => '6',
            'limit_gallery' => '12',
            'section_hero' => '1',
            'section_features' => '1',
            'section_works' => '0',
            'section_area' => '1',
            'section_testimonials' => '0',
            'section_faq' => '0',
            'section_cta' => '1',
            'urgency_enabled' => '0',
            'appearance_look' => 'azure-blast',
            'appearance_theme' => 'azul',
            'appearance_font' => 'geometric',
            'appearance_layout' => 'frame',
            'appearance_media' => 'media-wide',
        ];
    }

    return [
        'site_plan' => 'plus',
        'feature_page_sobre' => '1',
        'feature_page_galeria' => '1',
        'feature_page_contato' => '1',
        'feature_animations' => '1',
        'feature_looks_premium' => '1',
        'feature_preset_nicho' => '1',
        'limit_services' => '10',
        'limit_gallery' => '20',
        'section_hero' => '1',
        'section_features' => '1',
        'section_works' => '1',
        'section_area' => '1',
        'section_testimonials' => '0',
        'section_faq' => '1',
        'section_cta' => '1',
        'urgency_enabled' => '1',
        'appearance_look' => 'xhybrid-signature',
        'appearance_theme' => 'preto',
        'appearance_font' => 'saas',
        'appearance_layout' => 'soft',
        'appearance_media' => 'classic',
    ];
}

function plan_apply(PDO $pdo, string $plan): void
{
    require_once __DIR__ . '/settings.php';
    settings_save_many($pdo, plan_bundle($plan));
}

/** Paletas liberadas por plano (looks). */
function plan_allowed_palettes(string $plan): array
{
    $plan = plan_normalize($plan);
    if ($plan === 'plus') {
        return ['Neutro', 'Azul', 'Ciano', 'Verde', 'Quente', 'Vermelho', 'Roxo'];
    }
    if ($plan === 'pleno') {
        return ['Neutro', 'Azul', 'Ciano', 'Verde', 'Quente'];
    }
    return ['Neutro'];
}

function plan_limit_int(array $settings, string $key, int $fallback): int
{
    $n = (int) ($settings[$key] ?? $fallback);
    return $n > 0 ? $n : $fallback;
}
