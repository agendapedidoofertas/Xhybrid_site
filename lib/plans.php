<?php

declare(strict_types=1);

/**
 * Planos comerciais: basic | medium | pro (aliases legado essencial/profissional/personalizado).
 */
function plan_ids(): array
{
    return ['basic', 'medium', 'pro'];
}

/**
 * @return array<string, string>
 */
function plan_aliases(): array
{
    return [
        'essencial' => 'basic',
        'profissional' => 'medium',
        'personalizado' => 'pro',
        'basic' => 'basic',
        'medium' => 'medium',
        'pro' => 'pro',
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

/** Edição de Marca (nome da empresa / identidade) — matriz do plano (default: só Pro). */
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
    return $plan === 'pro';
}

function plan_labels(): array
{
    return [
        'basic' => 'Basic',
        'medium' => 'Medium',
        'pro' => 'Pro',
    ];
}

/**
 * Texto curto para UI (todos os planos neste deploy usam subcaminho 8xd.com.br).
 *
 * @return array<string, string>
 */
function plan_blurbs(): array
{
    return [
        'basic' => 'Subcaminho 8xd.com.br · site completo · personalização mínima',
        'medium' => 'Subcaminho 8xd.com.br · personalização limitada · sem edição de Marca pelo cliente',
        'pro' => 'Subcaminho 8xd.com.br · personalização total · Marca e looks premium · manutenção mensal maior',
    ];
}

/**
 * Pacote padrão de cada plano (settings keys).
 *
 * @return array<string, string>
 */
function plan_bundle(string $plan): array
{
    $plan = plan_normalize($plan);

    // Basic: mesmo esqueleto de páginas; limita quantidade/funções (não corta Sobre/Projetos).
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

    // Medium: mesmo site; animação + 1 bloco extra; looks premium off (personalização limitada).
    if ($plan === 'medium') {
        return [
            'site_plan' => 'medium',
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

    // Pro: mesmo site do Medium; personalização total (looks premium) + FAQ/urgência; tetos finitos (Drive).
    return [
        'site_plan' => 'pro',
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
    if ($plan === 'pro') {
        return ['Neutro', 'Azul', 'Ciano', 'Verde', 'Quente', 'Vermelho', 'Roxo'];
    }
    if ($plan === 'medium') {
        return ['Neutro', 'Azul', 'Ciano', 'Verde', 'Quente'];
    }
    return ['Neutro'];
}

function plan_limit_int(array $settings, string $key, int $fallback): int
{
    $n = (int) ($settings[$key] ?? $fallback);
    return $n > 0 ? $n : $fallback;
}
