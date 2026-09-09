<?php

declare(strict_types=1);

/**
 * Planos comerciais e pacotes de flags.
 */
function plan_ids(): array
{
    return ['essencial', 'profissional', 'personalizado'];
}

function plan_labels(): array
{
    return [
        'essencial' => 'Essencial (R$600–700)',
        'profissional' => 'Profissional (R$800–1.000)',
        'personalizado' => 'Personalizado (R$1.200–1.500+)',
    ];
}

/**
 * Pacote padrão de cada plano (settings keys).
 */
function plan_bundle(string $plan): array
{
    $plan = in_array($plan, plan_ids(), true) ? $plan : 'essencial';

    if ($plan === 'essencial') {
        return [
            'site_plan' => 'essencial',
            'feature_page_sobre' => '0',
            'feature_page_galeria' => '0',
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

    if ($plan === 'profissional') {
        return [
            'site_plan' => 'profissional',
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
            'section_works' => '1',
            'section_area' => '1',
            'section_testimonials' => '1',
            'section_faq' => '0',
            'section_cta' => '1',
            'urgency_enabled' => '1',
            'appearance_look' => 'azure-blast',
            'appearance_theme' => 'azul',
            'appearance_font' => 'geometric',
            'appearance_layout' => 'frame',
            'appearance_media' => 'media-wide',
        ];
    }

    return [
        'site_plan' => 'personalizado',
        'feature_page_sobre' => '1',
        'feature_page_galeria' => '1',
        'feature_page_contato' => '1',
        'feature_animations' => '1',
        'feature_looks_premium' => '1',
        'feature_preset_nicho' => '1',
        'limit_services' => '99',
        'limit_gallery' => '99',
        'section_hero' => '1',
        'section_features' => '1',
        'section_works' => '1',
        'section_area' => '1',
        'section_testimonials' => '1',
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
    if ($plan === 'personalizado') {
        return ['Neutro', 'Azul', 'Ciano', 'Verde', 'Quente', 'Vermelho', 'Roxo'];
    }
    if ($plan === 'profissional') {
        return ['Neutro', 'Azul', 'Ciano', 'Verde', 'Quente'];
    }
    return ['Neutro'];
}

function plan_limit_int(array $settings, string $key, int $fallback): int
{
    $n = (int) ($settings[$key] ?? $fallback);
    return $n > 0 ? $n : $fallback;
}
